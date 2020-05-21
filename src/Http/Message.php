<?php
namespace  MessiasDias\PHPLibrary\Http;
use Psr\Http\Message\MessageInterface;

/**
 * Class messiasdias\php_library\Http\Message
 */

class Message implements MessageInterface {


    private $version, $suported_versions = ["1.0", "1.1"];


    public function __construct(){
        $this->version = isset($_SERVER['SERVER_PROTOCOL']) ? explode('/', strtolower($_SERVER['SERVER_PROTOCOL'] ))[1] : '1.0';
    }
   
    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.0", "1.1").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }


     /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        try{
            if( in_array($verson, $this->suported_versions) ){
                $this->version = (string) $version;
                return $this;
            }else{
                throw new InvalidArgumentExcepition("Unsupported Version. Try '1.0' or '1.1'.");
            }
        }catch(\InvalidArgumentExcepition $e){
            return $e;
        }
    }


     /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {

    }

}