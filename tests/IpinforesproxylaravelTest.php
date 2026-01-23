<?php
namespace ipinfo\ipinfolaravel\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ipinfo\ipinfolaravel\iphandler\IPHandlerInterface;
use ipinfo\ipinfolaravel\resproxy\ipinforesproxylaravel;
use ipinfo\ipinfo\IPinfo;

class IpinforesproxylaravelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \ipinfo\ipinfolaravel\resproxy\ipinforesproxylaravelServiceProvider::class,
        ];
    }

    /** Create a middleware with injected mocks, stubbing configure() */
    protected function makeMiddleware(
        $client,
        $selector,
        $filter = null,
        $noExcept = false,
    ) {
        $mw = $this->getMockBuilder(ipinforesproxylaravel::class)
            ->onlyMethods(["configure"])
            ->getMock();
        $mw->method("configure")->willReturn(null);
        $mw->ipinfo = $client;
        $mw->ip_selector = $selector;
        $mw->filter = $filter;
        $mw->no_except = $noExcept;
        return $mw;
    }

    public function test_handle_merges_resproxy_details_on_success()
    {
        $details = (object) [
            "ip" => "175.107.211.204",
            "last_seen" => "2026-01-15",
            "percent_days_seen" => 100,
            "service" => "test_service",
        ];
        $client = $this->createMock(IPinfo::class);
        $client
            ->expects($this->once())
            ->method("getResproxy")
            ->with("175.107.211.204")
            ->willReturn($details);

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("175.107.211.204");

        $mw = $this->makeMiddleware($client, $selector);

        $request = Request::create("/foo", "GET");
        $next = function ($req) use (&$out) {
            $out = $req->get("ipinfo_resproxy");
            return new Response("OK", 200);
        };

        $resp = $mw->handle($request, $next);

        $this->assertSame($details, $out);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals("OK", $resp->getContent());
    }

    public function test_handle_skips_lookup_when_filter_true()
    {
        $client = $this->createMock(IPinfo::class);
        $client->expects($this->never())->method("getResproxy");

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->expects($this->never())->method("getIP");

        $filter = fn($req) => true;
        $mw = $this->makeMiddleware($client, $selector, $filter);

        $request = Request::create("/", "GET");
        $request->headers->set("user-agent", "GoogleBot");

        $next = function ($req) use (&$out) {
            $out = $req->get("ipinfo_resproxy");
            return new Response();
        };

        $mw->handle($request, $next);
        $this->assertNull($out);
    }

    public function test_handle_throws_if_client_throws_and_no_except_false()
    {
        $this->expectException(\Exception::class);

        $client = $this->createMock(IPinfo::class);
        $client
            ->method("getResproxy")
            ->willThrowException(new \Exception("boom"));

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("1.1.1.1");

        $mw = $this->makeMiddleware($client, $selector, null, false);
        $mw->handle(Request::create("/", "GET"), fn($r) => new Response());
    }

    public function test_handle_swallows_if_client_throws_and_no_except_true()
    {
        $client = $this->createMock(IPinfo::class);
        $client
            ->method("getResproxy")
            ->willThrowException(new \Exception("boom"));

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("1.1.1.1");

        $mw = $this->makeMiddleware($client, $selector, null, true);

        $next = function ($req) use (&$out) {
            $out = $req->get("ipinfo_resproxy");
            return new Response();
        };

        $mw->handle(Request::create("/", "GET"), $next);
        $this->assertNull($out);
    }

    public function test_defaultFilter_detects_bots_and_spiders()
    {
        $mw = new ipinforesproxylaravel();

        $r1 = Request::create("/", "GET");
        $r1->headers->set("user-agent", "MySpider");
        $this->assertTrue($mw->defaultFilter($r1));

        $r2 = Request::create("/", "GET");
        $r2->headers->set("user-agent", "someBOT/2.0");
        $this->assertTrue($mw->defaultFilter($r2));

        $r3 = Request::create("/", "GET");
        $r3->headers->set("user-agent", "normal");
        $this->assertFalse($mw->defaultFilter($r3));

        $r4 = Request::create("/", "GET");
        $this->assertFalse($mw->defaultFilter($r4));
    }

    public function test_handle_empty_response()
    {
        // Empty object simulates IP not in resproxy database
        $details = (object) [];
        $client = $this->createMock(IPinfo::class);
        $client
            ->expects($this->once())
            ->method("getResproxy")
            ->with("175.107.211.204")
            ->willReturn($details);

        $selector = $this->createMock(IPHandlerInterface::class);
        $selector->method("getIP")->willReturn("175.107.211.204");

        $mw = $this->makeMiddleware($client, $selector);

        $request = Request::create("/foo", "GET");
        $next = function ($req) use (&$out) {
            $out = $req->get("ipinfo_resproxy");
            return new Response("OK", 200);
        };

        $resp = $mw->handle($request, $next);

        $this->assertSame($details, $out);
        $this->assertEquals(200, $resp->getStatusCode());
    }
}
