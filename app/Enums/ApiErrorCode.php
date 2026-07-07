<?php

namespace App\Enums;

enum ApiErrorCode: string
{
    /** @since 1.0.0 */
    case AUTH_REQUIRED = 'AUTH_REQUIRED';
    /** @since 1.0.0 */
    case NONCE_REPLAY = 'NONCE_REPLAY';
    /** @since 1.0.0 */
    case TIMESTAMP_OUT_OF_WINDOW = 'TIMESTAMP_OUT_OF_WINDOW';
    /** @since 1.0.0 */
    case DEVICE_MISMATCH = 'DEVICE_MISMATCH';
    /** @since 1.0.0 */
    case DEVICE_NOT_BOUND = 'DEVICE_NOT_BOUND';
    /** @since 1.0.0 */
    case LICENSE_INVALID = 'LICENSE_INVALID';
    /** @since 1.0.0 */
    case LICENSE_INEFFECTIVE = 'LICENSE_INEFFECTIVE';
    /** @since 1.0.0 */
    case INVALID_CHANNEL = 'INVALID_CHANNEL';
    /** @since 1.0.0 */
    case INVALID_VERSION = 'INVALID_VERSION';
    /** @since 1.0.0 */
    case PACKAGE_NOT_FOUND = 'PACKAGE_NOT_FOUND';
    /** @since 1.0.0 */
    case RATE_LIMITED = 'RATE_LIMITED';
    /** @since 1.0.0 */
    case VALIDATION_FAILED = 'VALIDATION_FAILED';
    /** @since 1.0.0 */
    case SERVER_ERROR = 'SERVER_ERROR';
}
