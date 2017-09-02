<?php
/**
 * Mage2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ind.purvesh@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://mage2.website for more information.
 *
 * @author    Purvesh <ind.purvesh@gmail.com>
 * @copyright 2016-2017 Mage2
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License v3.0
 */
namespace Mage2\Install\Controllers;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Mage2\User\Models\AdminUser;
use Mage2\Framework\System\Controllers\Controller;
use Mage2\Install\Requests\AdminUserRequest;
use Mage2\User\Models\Role;
use Mage2\Dashboard\Models\Configuration;
use Mage2\Framework\Module\Facades\Module;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use DB;

class InstallController extends Controller
{

    public $extensions = [
        'openssl',
        'pdo',
        'mbstring',
        'tokenizer',
        'xml',
        'curl',];

    public function index()
    {
        Session::forget('install-module');

        $result = [];
        foreach ($this->extensions as $ext) {
            if (extension_loaded($ext)) {
                $result [$ext] = 'true';
            } else {
                $result [$ext] = false;
            }
        }


        return view('mage2-install::install.extension')->with('result', $result);
    }

    public function databaseTableGet()
    {
        return view('mage2-install::install.database-table');
    }

    public function databaseTablePost(Request $request)
    {
            try {
                //DB::beginTransaction();
                Artisan::call('migrate');
            } catch (Exception $e) {
                //DB::rollback();
                throw new Exception($e->getMessage());
            }
        //DB::commit();
        return redirect()->route('mage2.install.admin');
    }

    public function databaseDataGet()
    {
        return view('mage2-install::install.database-data');
    }

    public function databaseDataPost(Request $request)
    {
        if ($request->get('install_data') == "yes") {

            $identifier = $request->get('identifier');
            $module = Module::get($identifier);
            $basePath = base_path();

            $moduleBasePath = $module->getPath() . DIRECTORY_SEPARATOR . "database";
            $dbPath = str_replace($basePath, "", $moduleBasePath);

            if (File::exists($moduleBasePath)) {
                try {
                    Artisan::call('migrate', ['--path' => $dbPath]);
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        }

        return redirect()->route('mage2.install.admin');
    }

    public function admin()
    {
        return view('mage2-install::install.admin');
    }

    public function adminPost(AdminUserRequest $request)
    {
        $role = Role::create(['name' => 'administraotr', 'description' => 'Administrator Role has all access']);

        AdminUser::create([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
            'is_super_admin' => 1,
            'role_id' => $role->id,
        ]);

        $host = str_replace('http://', '', $request->getUriForPath(''));
        $host = str_replace('https://', '', $host);


        Configuration::create(['configuration_key' => 'active_theme_identifier',
            'configuration_value' => 'mage2-basic']);

        Configuration::create(['configuration_key' => 'active_theme_path',
            'configuration_value' => base_path('themes\mage2\basic')]);
        Configuration::create(['configuration_key' => 'mage2_catalog_no_of_product_category_page',
            'configuration_value' => 9]);
        Configuration::create(['configuration_key' => 'mage2_catalog_cart_page_display_taxamount',
            'configuration_value' => 'yes']);
        Configuration::create(['configuration_key' => 'mage2_tax_class_percentage_of_tax',
            'configuration_value' => 15]);

        return redirect()->route('mage2.install.success');
    }

    public function success()
    {
        Storage::disk('local')->put('installed.txt', '.installed');
        return view('mage2-install::install.success');
    }

}
