<?php
namespace ipinfo\ipinfolaravel\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ipinfo\ipinfo\IPinfo as IPinfoClient;
use ipinfo\ipinfolaravel\iphandler\IPHandlerInterface;
use Orchestra\Testbench\TestCase;
use ipinfo\ipinfolaravel\ipinfolaravel;

class IpinfolaravelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\ipinfo\ipinfolaravel\ipinfolaravelServiceProvider::class];
    }

    /** Helpers **/
    protected function makeMiddlewareWithMocks(
        $client,
        $selector,
        $filter = null,
        $noExcept = false,
    ) {
        // stub out configure() so it doesn't overwrite our mocks
        $mw = $this->getMockBuilder(ipinfolaravel::class)
            ->onlyMethods(["configure"])
            ->getMock();
        $mw->method("configure")->willReturn(null);
        $mw->ipinfo = $client;
        $mw->ip_selector = $selector;
        $mw->filter = $filter;
        $mw->no_except = $noExcept;
        return $mw;
    }

    public function test_handle_merges_details_on_success()
    {
        // mock IPinfoClient
        $details = (object) ["city" => "TestCity", "ip" => "1.2.3.4"];
        $client = $this->createMock(IPinfoClient::class);
        $client
            ->expects($this->once())
            ->method("getDetails")
            ->with("1.2.3.4")
            ->willReturn($details);

        // mock IP selector
        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("1.2.3.4");

        $mw = $this->makeMiddlewareWithMocks($client, $selector);

        $request = Request::create("/foo", "GET");
        $captured = null;
        $next = function ($req) use (&$captured) {
            $captured = $req->get("ipinfo");
            return new Response("OK", 200);
        };

        $resp = $mw->handle($request, $next);

        $this->assertSame($details, $captured);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals("OK", $resp->getContent());
    }

    public function test_handle_skips_lookup_when_filter_returns_true()
    {
        $client = $this->createMock(IPinfoClient::class);
        $client->expects($this->never())->method("getDetails");

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->expects($this->never())->method("getIP");

        $filter = fn($req) => true;

        $mw = $this->makeMiddlewareWithMocks($client, $selector, $filter);

        $request = Request::create("/foo", "GET");
        $request->headers->set("user-agent", "my-bot");

        $captured = "unset";
        $next = function ($req) use (&$captured) {
            $captured = $req->get("ipinfo");
            return new Response();
        };

        $mw->handle($request, $next);
        $this->assertNull($captured);
    }

    public function test_handle_throws_if_client_throws_and_no_except_false()
    {
        $this->expectException(\Exception::class);

        $client = $this->createMock(IPinfoClient::class);
        $client
            ->method("getDetails")
            ->willThrowException(new \Exception("fail"));

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("1.2.3.4");

        $mw = $this->makeMiddlewareWithMocks($client, $selector, null, false);

        $mw->handle(Request::create("/", "GET"), function () {});
    }

    public function test_handle_swallows_if_client_throws_and_no_except_true()
    {
        $client = $this->createMock(IPinfoClient::class);
        $client
            ->method("getDetails")
            ->willThrowException(new \Exception("fail"));

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("1.2.3.4");

        $mw = $this->makeMiddlewareWithMocks($client, $selector, null, true);

        $captured = "unset";
        $next = function ($req) use (&$captured) {
            $captured = $req->get("ipinfo");
            return new Response();
        };

        $mw->handle(Request::create("/", "GET"), $next);
        $this->assertNull($captured);
    }

    public function test_defaultFilter_detects_bots_and_spiders()
    {
        $mw = new ipinfolaravel();

        $r1 = Request::create("/", "GET");
        $r1->headers->set("user-agent", "GoogleBot");
        $this->assertTrue($mw->defaultFilter($r1));

        $r2 = Request::create("/", "GET");
        $r2->headers->set("user-agent", "SomeSpider/1.0");
        $this->assertTrue($mw->defaultFilter($r2));

        $r3 = Request::create("/", "GET");
        $r3->headers->set("user-agent", "Mozilla/5.0");
        $this->assertFalse($mw->defaultFilter($r3));

        $r4 = Request::create("/", "GET");
        $this->assertFalse($mw->defaultFilter($r4));
    }
}
