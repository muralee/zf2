<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Http_Response
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @namespace
 */
namespace ZendTest\HTTP;
use Zend\HTTP\Response;

/**
 * Test helper
 */

/**
 * Zend_Http_Response
 */

/**
 * PHPUnit test case
 */

/**
 * Zend_Http_Response unit tests
 *
 * @category   Zend
 * @package    Zend_Http_Response
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Http
 * @group      Zend_Http_Response
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    { }

    public function testGzipResponse ()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_gzip');

        $res = Response\Response::fromString($response_text);

        $this->assertEquals('gzip', $res->getHeader('Content-encoding'));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('f24dd075ba2ebfb3bf21270e3fdc5303', md5($res->getRawBody()));
    }

    public function testDeflateResponse ()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_deflate');

        $res = Response\Response::fromString($response_text);

        $this->assertEquals('deflate', $res->getHeader('Content-encoding'));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('ad62c21c3aa77b6a6f39600f6dd553b8', md5($res->getRawBody()));
    }

    /**
     * Make sure wer can handle non-RFC complient "deflate" responses.
     *
     * Unlike stanrdard 'deflate' response, those do not contain the zlib header
     * and trailer. Unfortunately some buggy servers (read: IIS) send those and
     * we need to support them.
     *
     * @link http://framework.zend.com/issues/browse/ZF-6040
     */
    public function testNonStandardDeflateResponseZF6040()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_deflate_iis');

        $res = Response\Response::fromString($response_text);

        $this->assertEquals('deflate', $res->getHeader('Content-encoding'));
        $this->assertEquals('d82c87e3d5888db0193a3fb12396e616', md5($res->getBody()));
        $this->assertEquals('c830dd74bb502443cf12514c185ff174', md5($res->getRawBody()));
    }

    public function testChunkedResponse ()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_chunked');

        $res = Response\Response::fromString($response_text);

        $this->assertEquals('chunked', $res->getHeader('Transfer-encoding'));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('c0cc9d44790fa2a58078059bab1902a9', md5($res->getRawBody()));
    }

    public function testChunkedResponseCaseInsensitiveZF5438()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_chunked_case');

        $res = Response\Response::fromString($response_text);

        $this->assertEquals('chunked', strtolower($res->getHeader('Transfer-encoding')));
        $this->assertEquals('0b13cb193de9450aa70a6403e2c9902f', md5($res->getBody()));
        $this->assertEquals('c0cc9d44790fa2a58078059bab1902a9', md5($res->getRawBody()));
    }


    public function testLineBreaksCompatibility()
    {
        $response_text_lf = $this->readResponse('response_lfonly');
        $res_lf = Response\Response::fromString($response_text_lf);

        $response_text_crlf = $this->readResponse('response_crlf');
        $res_crlf = Response\Response::fromString($response_text_crlf);

        $this->assertEquals($res_lf->getHeadersAsString(true), $res_crlf->getHeadersAsString(true), 'Responses headers do not match');
        $this->assertEquals($res_lf->getBody(), $res_crlf->getBody(), 'Response bodies do not match');
    }

    public function testExtractMessageCrlf()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_crlf');
        $this->assertEquals("OK", Response\Response::extractMessage($response_text), "Response message is not 'OK' as expected");
    }

    public function testExtractMessageLfonly()
    {
        $response_text = file_get_contents(dirname(__FILE__) . '/_files/response_lfonly');
        $this->assertEquals("OK", Response\Response::extractMessage($response_text), "Response message is not 'OK' as expected");
    }

    public function test404IsError()
    {
        $response_text = $this->readResponse('response_404');
        $response = Response\Response::fromString($response_text);

        $this->assertEquals(404, $response->getStatus(), 'Response code is expected to be 404, but it\'s not.');
        $this->assertTrue($response->isError(), 'Response is an error, but isError() returned false');
        $this->assertFalse($response->isSuccessful(), 'Response is an error, but isSuccessful() returned true');
        $this->assertFalse($response->isRedirect(), 'Response is an error, but isRedirect() returned true');
    }

    public function test500isError()
    {
        $response_text = $this->readResponse('response_500');
        $response = Response\Response::fromString($response_text);

        $this->assertEquals(500, $response->getStatus(), 'Response code is expected to be 500, but it\'s not.');
        $this->assertTrue($response->isError(), 'Response is an error, but isError() returned false');
        $this->assertFalse($response->isSuccessful(), 'Response is an error, but isSuccessful() returned true');
        $this->assertFalse($response->isRedirect(), 'Response is an error, but isRedirect() returned true');
    }

    public function test300isRedirect()
    {
        $response = Response\Response::fromString($this->readResponse('response_302'));

        $this->assertEquals(302, $response->getStatus(), 'Response code is expected to be 302, but it\'s not.');
        $this->assertTrue($response->isRedirect(), 'Response is a redirection, but isRedirect() returned false');
        $this->assertFalse($response->isError(), 'Response is a redirection, but isError() returned true');
        $this->assertFalse($response->isSuccessful(), 'Response is a redirection, but isSuccessful() returned true');
    }

    public function test200Ok()
    {
        $response = Response\Response::fromString($this->readResponse('response_deflate'));

        $this->assertEquals(200, $response->getStatus(), 'Response code is expected to be 200, but it\'s not.');
        $this->assertFalse($response->isError(), 'Response is OK, but isError() returned true');
        $this->assertTrue($response->isSuccessful(), 'Response is OK, but isSuccessful() returned false');
        $this->assertFalse($response->isRedirect(), 'Response is OK, but isRedirect() returned true');
    }

    public function test100Continue()
    {
        $this->markTestIncomplete();
    }

    public function testAutoMessageSet()
    {
        $response = Response\Response::fromString($this->readResponse('response_403_nomessage'));

        $this->assertEquals(403, $response->getStatus(), 'Response status is expected to be 403, but it isn\'t');
        $this->assertEquals('Forbidden', $response->getMessage(), 'Response is 403, but message is not "Forbidden" as expected');

        // While we're here, make sure it's classified as error...
        $this->assertTrue($response->isError(), 'Response is an error, but isError() returned false');
        $this->assertFalse($response->isSuccessful(), 'Response is an error, but isSuccessful() returned true');
        $this->assertFalse($response->isRedirect(), 'Response is an error, but isRedirect() returned true');
    }

    public function testAsString()
    {
        $response_str = $this->readResponse('response_404');
        $response = Response\Response::fromString($response_str);

        $this->assertEquals(strtolower($response_str), strtolower($response->asString()), 'Response convertion to string does not match original string');
        $this->assertEquals(strtolower($response_str), strtolower((string)$response), 'Response convertion to string does not match original string');
    }

    public function testGetHeaders()
    {
        $response = Response\Response::fromString($this->readResponse('response_deflate'));
        $headers = $response->getHeaders();

        $this->assertEquals(8, count($headers), 'Header count is not as expected');
        $this->assertEquals('Apache', $headers['Server'], 'Server header is not as expected');
        $this->assertEquals('deflate', $headers['Content-encoding'], 'Content-type header is not as expected');
    }

    public function testGetVersion()
    {
        $response = Response\Response::fromString($this->readResponse('response_chunked'));
        $this->assertEquals(1.1, $response->getVersion(), 'Version is expected to be 1.1');
    }

    public function testResponseCodeAsText()
    {
        // This is an entirely static test

        // Test some response codes
        $this->assertEquals('Continue', Response\Response::responseCodeAsText(100));
        $this->assertEquals('OK', Response\Response::responseCodeAsText(200));
        $this->assertEquals('Multiple Choices', Response\Response::responseCodeAsText(300));
        $this->assertEquals('Bad Request', Response\Response::responseCodeAsText(400));
        $this->assertEquals('Internal Server Error', Response\Response::responseCodeAsText(500));

        // Make sure that invalid codes return 'Unkown'
        $this->assertEquals('Unknown', Response\Response::responseCodeAsText(600));

        // Check HTTP/1.0 value for 302
        $this->assertEquals('Found', Response\Response::responseCodeAsText(302));
        $this->assertEquals('Moved Temporarily', Response\Response::responseCodeAsText(302, false));

        // Check we get an array if no code is passed
        $codes = Response\Response::responseCodeAsText();
        $this->assertType('array', $codes);
        $this->assertEquals('OK', $codes[200]);
    }

    public function testUnknownCode()
    {
        $response_str = $this->readResponse('response_unknown');
        $response = Response\Response::fromString($response_str);

        // Check that dynamically the message is parsed
        $this->assertEquals(550, $response->getStatus(), 'Status is expected to be a non-standard 550');
        $this->assertEquals('Printer On Fire', $response->getMessage(), 'Message is expected to be extracted');

        // Check that statically, an Unknown string is returned for the 550 code
        $this->assertEquals('Unknown', Response\Response::responseCodeAsText($response_str));
    }

    public function testMultilineHeader()
    {
        $response = Response\Response::fromString($this->readResponse('response_multiline_header'));

        // Make sure we got the corrent no. of headers
        $this->assertEquals(6, count($response->getHeaders()), 'Header count is expected to be 6');

        // Check header integrity
        $this->assertEquals('timeout=15, max=100', $response->getHeader('keep-alive'));
        $this->assertEquals('text/html; charset=iso-8859-1', $response->getHeader('content-type'));
    }

    public function testExceptInvalidChunkedBody()
    {
        try {
            Response\Response::decodeChunkedBody($this->readResponse('response_deflate'));
            $this->fail('An expected exception was not thrown');
        } catch (\Zend\HTTP\Exception $e) {
            // We are ok!
        }
    }

    public function testExtractorsOnInvalidString()
    {
        // Try with an empty string
        $response_str = '';

        $this->assertTrue(Response\Response::extractCode($response_str) === false);
        $this->assertTrue(Response\Response::extractMessage($response_str) === false);
        $this->assertTrue(Response\Response::extractVersion($response_str) === false);
        $this->assertTrue(Response\Response::extractBody($response_str) === '');
        $this->assertTrue(Response\Response::extractHeaders($response_str) === array());
    }

    /**
     * Make sure a response with some leading whitespace in the response body
     * does not get modified (see ZF-1924)
     *
     */
    public function testLeadingWhitespaceBody()
    {
        $body = Response\Response::extractBody($this->readResponse('response_leadingws'));
        $this->assertEquals($body, "\r\n\t  \n\r\tx", 'Extracted body is not identical to expected body');
    }

    /**
     * Test that parsing a multibyte-encoded chunked response works.
     *
     * This can potentially fail on different PHP environments - for example
     * when mbstring.func_overload is set to overload strlen().
     *
     */
    public function testMultibyteChunkedResponse()
    {
        $md5 = 'ab952f1617d0e28724932401f2d3c6ae';

        $response = Response\Response::fromString($this->readResponse('response_multibyte_body'));
        $this->assertEquals($md5, md5($response->getBody()));
    }

    /**
     * Helper function: read test response from file
     *
     * @param string $response
     * @return string
     */
    protected function readResponse($response)
    {
        return file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $response);
    }
}
