<?php
/**
 * File containing the {@link RequestHelper_CURL} class.
 * 
 * @package Application Utils
 * @subpackage RequestHelper
 * @see RequestHelper_CURL
 */

declare(strict_types=1);

namespace AppUtils;

/**
 * Stores CURL error constants to be able to more easily
 * detect the type of error that occurred, since these are
 * not available in PHP. 
 *
 * @package Application Utils
 * @subpackage RequestHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class RequestHelper_CURL
{
    public const OK = 0;
    public const UNSUPPORTED_PROTOCOL = 1;
    public const FAILED_INIT = 2;
    public const URL_MALFORMAT = 3;
    public const NOT_BUILT_IN = 4;
    public const COULDNT_RESOLVE_PROXY = 5;
    public const COULDNT_RESOLVE_HOST = 6;
    public const COULDNT_CONNECT = 7;
    public const WEIRD_SERVER_REPLY = 8;
    public const REMOTE_ACCESS_DENIED = 9;
    public const FTP_ACCEPT_FAILED = 10;
    public const FTP_WEIRD_PASS_REPLY = 11;
    public const FTP_ACCEPT_TIMEOUT = 12;
    public const FTP_WEIRD_PASV_REPLY = 13;
    public const FTP_WEIRD_227_FORMAT = 14;
    public const FTP_CANT_GET_HOST = 15;
    public const HTTP2 = 16;
    public const FTP_COULDNT_SET_TYPE = 17;
    public const PARTIAL_FILE = 18;
    public const FTP_COULDNT_RETR_FILE = 19;
    public const QUOTE_ERROR = 21;
    public const HTTP_RETURNED_ERROR = 22;
    public const WRITE_ERROR = 23;
    public const UPLOAD_FAILED = 25;
    public const READ_ERROR = 26;
    public const OUT_OF_MEMORY = 27;
    public const OPERATION_TIMEDOUT = 28;
    public const FTP_PORT_FAILED = 30;
    public const FTP_COULDNT_USE_REST = 31;
    public const RANGE_ERROR = 33;
    public const HTTP_POST_ERROR = 34;
    public const SSL_CONNECT_ERROR = 35;
    public const BAD_DOWNLOAD_RESUME = 36;
    public const FILE_COULDNT_READ_FILE = 37;
    public const LDAP_CANNOT_BIND = 38;
    public const LDAP_SEARCH_FAILED = 39;
    public const FUNCTION_NOT_FOUND = 41;
    public const ABORTED_BY_CALLBACK = 42;
    public const BAD_FUNCTION_ARGUMENT = 43;
    public const INTERFACE_FAILED = 45;
    public const TOO_MANY_REDIRECTS = 47;
    public const UNKNOWN_OPTION = 48;
    public const TELNET_OPTION_SYNTAX = 49;
    public const GOT_NOTHING = 52;
    public const SSL_ENGINE_NOTFOUND = 53;
    public const SSL_ENGINE_SETFAILED = 54;
    public const SEND_ERROR = 55;
    public const RECV_ERROR = 56;
    public const SSL_CERTPROBLEM = 58;
    public const SSL_CIPHER = 59;
    public const PEER_FAILED_VERIFICATION = 60;
    public const BAD_CONTENT_ENCODING = 61;
    public const LDAP_INVALID_URL = 62;
    public const FILESIZE_EXCEEDED = 63;
    public const USE_SSL_FAILED = 64;
    public const SEND_FAIL_REWIND = 65;
    public const SSL_ENGINE_INITFAILED = 66;
    public const LOGIN_DENIED = 67;
    public const TFTP_NOTFOUND = 68;
    public const TFTP_PERM = 69;
    public const REMOTE_DISK_FULL = 70;
    public const TFTP_ILLEGAL = 71;
    public const TFTP_UNKNOWNID = 72;
    public const REMOTE_FILE_EXISTS = 73;
    public const TFTP_NOSUCHUSER = 74;
    public const CONV_FAILED = 75;
    public const CONV_REQD = 76;
    public const SSL_CACERT_BADFILE = 77;
    public const REMOTE_FILE_NOT_FOUND = 78;
    public const SSH = 79;
    public const SSL_SHUTDOWN_FAILED = 80;
    public const AGAIN = 81;
    public const SSL_CRL_BADFILE = 82;
    public const SSL_ISSUER_ERROR = 83;
    public const FTP_PRET_FAILED = 84;
    public const RTSP_CSEQ_ERROR = 85;
    public const RTSP_SESSION_ERROR = 86;
    public const FTP_BAD_FILE_LIST = 87;
    public const CHUNK_FAILED = 88;
    public const NO_CONNECTION_AVAILABLE = 89;
    public const SSL_PINNEDPUBKEYNOTMATCH = 90;
    public const SSL_INVALIDCERTSTATUS = 91;
    public const HTTP2_STREAM = 92;
    public const RECURSIVE_API_CALL = 93;
    public const AUTH_ERROR = 94;
    public const HTTP3 = 95;
    public const QUIC_CONNECT_ERROR = 96;
}
