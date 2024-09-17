<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        setcookie('haskey', base64_encode(env('APP_KEY')), time() + 3600, "/"); 
        setcookie('apiKey', $this->encryptString(env('APP_KEY'),env('FIREBASE_APIKEY')), time() + 3600, "/"); 
        setcookie('authDomain', $this->encryptString(env('APP_KEY'),env('FIREBASE_AUTH_DOMAIN')), time() + 3600, "/"); 
        setcookie('databaseURL', $this->encryptString(env('APP_KEY'),env('FIREBASE_DATABASE_URL')), time() + 3600, "/"); 
        setcookie('projectId', $this->encryptString(env('APP_KEY'),env('FIREBASE_PROJECT_ID')), time() + 3600, "/"); 
        setcookie('storageBucket', $this->encryptString(env('APP_KEY'),env('FIREBASE_STORAGE_BUCKET')), time() + 3600, "/"); 
        setcookie('messagingSenderId', $this->encryptString(env('APP_KEY'),env('FIREBASE_MESSAAGING_SENDER_ID')), time() + 3600, "/"); 
        setcookie('appId', $this->encryptString(env('APP_KEY'),env('FIREBASE_APP_ID')), time() + 3600, "/"); 
        setcookie('measurementId', $this->encryptString(env('APP_KEY'),env('FIREBASE_MEASUREMENT_ID')), time() + 3600, "/"); 

        $countries_data = [];
        $get_countries_json = file_get_contents(public_path('countriesdata.json'));
        if($get_countries_json != ''){
            $countries_data = json_decode($get_countries_json);
        }
        view()->composer('*', function($view) use($countries_data) {
            $view->with('countries_data', $countries_data);
        });
    }

    public function encryptString($passphrase,$value){
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx.$passphrase.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
        return json_encode($data);
    }
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
