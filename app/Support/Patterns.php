<?php

namespace App\Support;

/**
 * Validation regexes matching the CHECK constraints in database/idrive_schema.sql.
 */
final class Patterns
{
    public const PHONE = 'regex:/^09[0-9]{9}$/';

    public const LICENSE_NO = 'regex:/^[A-Z0-9][A-Z0-9-]{5,19}$/i';

    public const PLATE_NUMBER = 'regex:/^[A-Z0-9]{2,4}-?[0-9]{3,4}$/i';

    public const ID_NUMBER = 'regex:/^[A-Z0-9-]{5,30}$/i';

    public const LICENSE_PHOTO = 'regex:/^data:image\/(jpeg|jpg|png|webp);base64,/i';

    public const EMAIL = 'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
}
