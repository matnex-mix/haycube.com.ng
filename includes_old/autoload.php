<?php

require_once 'framework/error.php';
require_once 'framework/framework.php';
require_once 'framework/analytics.php';
require_once 'framework/response.php';
require_once 'framework/template.php';
require_once 'framework/page.php';
require_once 'framework/functions.php';
require_once 'framework/recovery.php';
require_once 'framework/language.php';

require_once 'database/db.php';
require_once 'database/filter.php';
require_once 'database/seeder.php';
require_once 'database/data.php';

require_once 'file/file.php';

require_once 'admin/admin.php';

/*
 * Define some functions to allow quick access
 *
 *
 */

use SPhp\Framework;
use SPhp\Database;

//class Error extends Framework\Error {}
class Template extends Framework\Template {}
class Page extends Framework\Page {}
class DB extends Database\DB {}
class Functions extends Framework\Functions {}
class F extends Functions {}
class File extends SPhp\File\File {}
class Admin extends SPhp\Admin\Admin {}
class L extends SPhp\Framework\Lang {}