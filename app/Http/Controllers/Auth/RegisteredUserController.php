<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Utility;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use app\Models\Plan;
use App\Models\GenerateOfferLetter;
use App\Models\JoiningLetter;
use App\Models\ExperienceCertificate;
use App\Models\NOC;
// use Twilio\Rest\Client;
use GuzzleHttp\Client;
// require_once 'vendor/autoload.php';
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     * @Produces("text/plain")
     */
    // protected $client;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function create($lang = '')
    {
        if ($lang == '') {
            $lang = \App\Models\Utility::getValByName('default_language');
        }

        \App::setLocale($lang);
        return view('auth.register', compact('lang'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        if (env('RECAPTCHA_MODULE') == 'yes') {
            $validation['g-recaptcha-response'] = 'required|captcha';
        } else {
            $validation = [];
        }
        $this->validate($request, $validation);



        $default_language = \DB::table('settings')->select('value')->where('name', 'default_language')->first();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $otp = rand(123456, 999999);
        $user = User::create([
            'name'  => $request->name,
            'email' => $request->email,
            'mobile'=> $request->mobile,
            'otp'   => $otp,
            'password' => Hash::make($request->password),
            'type'  => 'company',
            'lang'  => !empty($default_language) ? $default_language->value : '',
            'plan'  => 2,
            'created_by' => 1,
        ]);

        // $this->exotel($request->mobile,$otp);
        // event(new Registered($user));

        Auth::login($user);

        try {

            event(new Registered($user));
            $role_r = Role::findByName('company');

            $user->assignRole($role_r);
            $user->userDefaultData($user->id);
            $user->userDefaultDataRegister($user->id);
            GenerateOfferLetter::defaultOfferLetterRegister($user->id);
            ExperienceCertificate::defaultExpCertificatRegister($user->id);
            JoiningLetter::defaultJoiningLetterRegister($user->id);
            NOC::defaultNocCertificateRegister($user->id);
        } catch (\Exception $e) {

            $user->delete();

            return redirect('/register/lang?')->with('status', __('Email SMTP settings does not configure so please contact to your site admin.'));
        }

        // return view('verify-otp',['mobile' => $request->mobile]);

        return redirect(RouteServiceProvider::HOME);

    }

    public function showRegistrationForm($lang = '')
    {
        if (empty($lang)) {
            $lang = Utility::getValByName('default_language');
        }

        \App::setLocale($lang);
        if (Utility::getValByName('disable_signup_button') == 'on') {
            return view('auth.register', compact('lang'));
        } else {
            return abort('404', 'Page not found');
        }
    }
    // public function otpVerification(Request $request){
    //     $user = User::where(['mobile' => $request->mobile,'otp'=> $request->otp ])->first();
    //     // // echo "<pre>"; print_r( $user); exit;
    //     // if(!empty($user)){
    //     //     $user->is_verified = 1;
    //     //     $user->save();
              
    //     //     Auth::login($user);
    //     //     return redirect()->route('dashboard');
    //     // }else{
    //     //     return view('verify-otp',['mobile' => $request->mobile])->with('error', 'OTP Not matched');
    //     // }

    //     $response = $this->client->post('Verifications/' . $request->input('mobile') . '/Verify', [
    //         'json' => [
    //             'otp' => $request->input('otp')
    //         ]
    //     ]);

    //     $data = json_decode($response->getBody(), true);
    //     if ($data['status'] == 'success') {
    //         Auth::login($user);
    //         return response()->json(['message' => 'OTP verification successful.']);
    //     } else {
    //         return response()->json(['message' => 'Invalid OTP.']);
    //     }


    // }
    
    // public function twilio($mobile, $otp){

    //     $sid    = "ACab6913e940c0cedd0ba3f45f35f8045a";
    //     $token  = "cbe14c204311b0ea18d694d0de797434";
    //     $from   =  "+1 607 317 6754";
    //     $twilio = new Client($sid, $token);

    //     $message = $message = "Your OTP is " . $otp;
    //     $twilio->messages->create($mobile, // to
    //         array(
    //                 "from" => $from,
    //                 "body" => $message
    //         )
    //     );
    //}

    
    // public function exotel($mobile,$otp)
    // {

    //     include('vendor/rmccue/requests/library/Requests.php');
    //     Requests::register_autoloader();
    //     $headers = array();
    //     $data = array(
    //         'From' => '0XXXXXX4890',
    //         'To' => $mobile,
    //         'Body' => $otp
    //     );
    //     $response = Requests::post('https://2b6f50958826bf749d38ed740fff7c92bb0e3964f57e5564:bf507557f4b26ec8fe6d122a80ddb7fca10ca5caafd0271aapi.exotel.com/v1/Accounts/idab61/Sms/send', $headers, $data);
    // }
}
