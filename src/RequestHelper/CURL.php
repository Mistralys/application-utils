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
    const OK = 0;
    const UNSUPPORTED_PROTOCOL = 1;
    const FAILED_INIT = 2;
    const URL_MALFORMAT = 3;
    const NOT_BUILT_IN = 4;
    const COULDNT_RESOLVE_PROXY = 5;
    const COULDNT_RESOLVE_HOST = 6;
    const COULDNT_CONNECT = 7;
    const WEIRD_SERVER_REPLY = 8;
    const REMOTE_ACCESS_DENIED = 9;
    const FTP_ACCEPT_FAILED = 10;
    const FTP_WEIRD_PASS_REPLY = 11;
    const FTP_ACCEPT_TIMEOUT = 12;
    const FTP_WEIRD_PASV_REPLY = 13;
    const FTP_WEIRD_227_FORMAT = 14;
    const FTP_CANT_GET_HOST = 15;
    const HTTP2 = 16;
    const FTP_COULDNT_SET_TYPE = 17;
    const PARTIAL_FILE = 18;
    const FTP_COULDNT_RETR_FILE = 19;
    const QUOTE_ERROR = 21;
    const HTTP_RETURNED_ERROR = 22;
    const WRITE_ERROR = 23;
    const UPLOAD_FAILED = 25;
    const READ_ERROR = 26;
    const OUT_OF_MEMORY = 27;
    const OPERATION_TIMEDOUT = 28;
    const FTP_PORT_FAILED = 30;
    const FTP_COULDNT_USE_REST = 31;
    const RANGE_ERROR = 33;
    const HTTP_POST_ERROR = 34;
    const SSL_CONNECT_ERROR = 35;
    const BAD_DOWNLOAD_RESUME = 36;
    const FILE_COULDNT_READ_FILE = 37;
    const LDAP_CANNOT_BIND = 38;
    const LDAP_SEARCH_FAILED = 39;
    const FUNCTION_NOT_FOUND = 41;
    const ABORTED_BY_CALLBACK = 42;
    const BAD_FUNCTION_ARGUMENT = 43;
    const INTERFACE_FAILED = 45;
    const TOO_MANY_REDIRECTS = 47;
    const UNKNOWN_OPTION = 48;
    const TELNET_OPTION_SYNTAX = 49;
    const GOT_NOTHING = 52;
    const SSL_ENGINE_NOTFOUND = 53;
    const SSL_ENGINE_SETFAILED = 54;
    const SEND_ERROR = 55;
    const RECV_ERROR = 56;
    const SSL_CERTPROBLEM = 58;
    const SSL_CIPHER = 59;
    const PEER_FAILED_VERIFICATION = 60;
    const BAD_CONTENT_ENCODING = 61;
    const LDAP_INVALID_URL = 62;
    const FILESIZE_EXCEEDED = 63;
    const USE_SSL_FAILED = 64;
    const SEND_FAIL_REWIND = 65;
    const SSL_ENGINE_INITFAILED = 66;
    const LOGIN_DENIED = 67;
    const TFTP_NOTFOUND = 68;
    const TFTP_PERM = 69;
    const REMOTE_DISK_FULL = 70;
    const TFTP_ILLEGAL = 71;
    const TFTP_UNKNOWNID = 72;
    const REMOTE_FILE_EXISTS = 73;
    const TFTP_NOSUCHUSER = 74;
    const CONV_FAILED = 75;
    const CONV_REQD = 76;
    const SSL_CACERT_BADFILE = 77;
    const REMOTE_FILE_NOT_FOUND = 78;
    const SSH = 79;
    const SSL_SHUTDOWN_FAILED = 80;
    const AGAIN = 81;
    const SSL_CRL_BADFILE = 82;
    const SSL_ISSUER_ERROR = 83;
    const FTP_PRET_FAILED = 84;
    const RTSP_CSEQ_ERROR = 85;
    const RTSP_SESSION_ERROR = 86;
    const FTP_BAD_FILE_LIST = 87;
    const CHUNK_FAILED = 88;
    const NO_CONNECTION_AVAILABLE = 89;
    const SSL_PINNEDPUBKEYNOTMATCH = 90;
    const SSL_INVALIDCERTSTATUS = 91;
    const HTTP2_STREAM = 92;
    const RECURSIVE_API_CALL = 93;
    const AUTH_ERROR = 94;
    const HTTP3 = 95;
    const QUIC_CONNECT_ERROR = 96;
}
