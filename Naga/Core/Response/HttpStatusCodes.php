<?php

namespace Naga\Core\Response;

use Naga\Core\nComponent;

/**
 * A class containing http status codes.
 *
 * @author BlindingLight<bloodredshade@gmail.com>
 * @package Naga\Core\Response
 */
final class HttpStatusCodes extends nComponent
{
	const StatusContinue = 100;
	const StatusSwitchingProtocols = 101;

	const StatusOk = 200;
	const StatusCreated = 201;
	const StatusAccepted = 202;
	const StatusNonAuthoritativeInformation = 203;
	const StatusNoContent = 204;
	const StatusResetContent = 205;
	const StatusPartialContent = 206;

	const StatusMultipleChoices = 300;
	const StatusMovedPermanently = 301;
	const StatusMovedTemporarily = 302;
	const StatusSeeOther = 303;
	const StatusNotModified = 304;
	const StatusUseProxy = 305;

	const StatusBadRequest = 400;
	const StatusUnauthorized = 401;
	const StatusPaymentRequired = 402;
	const StatusForbidden = 403;
	const StatusNotFound = 404;
	const StatusMethodNotAllowed = 405;
	const StatusNotAcceptable = 406;
	const StatusProxyAuthenticationRequired = 407;
	const StatusRequestTimeout = 408;
	const StatusConflict = 409;
	const StatusGone = 410;
	const StatusLengthRequired = 411;
	const StatusPreconditionFailed = 412;
	const StatusRequestEntityTooLarge = 413;
	const StatusRequestUriTooLarge = 414;
	const StatusUnsupportedMediaType = 415;

	const StatusInternalServerError = 500;
	const StatusNotImplemented = 501;
	const StatusBadGateway = 502;
	const StatusServiceUnavailable = 503;
	const StatusGatewayTimeout = 504;
	const StatusHttpVersionNotSupported = 505;
}