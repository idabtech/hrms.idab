<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Controller;
use App\Models\Userauth;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getallheaders(\Illuminate\Http\Request $request)
    {
        $headers = $request->header('Authorization');
        return $headers;
    }

    // public function createToken(){
    //     $post = json_decode(file_get_contents('php://input', 'r'));

    //     if(isset($post->user_id) && $post->user_id != ''){
    //         $auth_key = md5(uniqid() . $post->user_id);
    //         $userauth = new Userauth();

    //         $userauth->user_id = $post->user_id;
    //         $userauth->auth_key = $auth_key;
    //         $userauth->save();


    //         print_r(json_encode(array(
    //             "status" => true,
    //             "message" => "Token Created Successfully",
    //             "result" => array(
    //                 "auth_key" => $auth_key,
    //                 'user_id' => $post->user_id
    //             )
    //         )));
    //         exit;
    //     }else{
    //         print_r(json_encode(array(
    //             "status" => false,
    //             "message" => "User Id Can't be empty"
    //         )));
    //         exit;
    //     }
    // }

    public function get_company_id()
    {

        $headers = getallheaders();

        
        if (isset($headers['Authorization']) && $headers['Authorization'] != '')
        {

            $token = explode('Bearer ',$headers['Authorization']);

            $userData = User::where('token', '=', $token[1])->first();

            if ($userData)
            {
                return $userData->id;
            }
            else
            {
                print_r(json_encode(array(
                    "status" => false,
                    "result" => "Invalid authentication.",
                )));
                exit;
            }

        }
        else
        {
            print_r(json_encode(array(
                "status" => false,
                "msg" => 'Auth key not found'
            )));
            exit;
        }
    }

    public function getJobDetails(Request $request)
    {
        $company_id = $this->get_company_id();

        $jobdetails = Job::select('jobs.id as job_id','jobs.title','jobs.description','users.name as created_by')->leftJoin('users','users.id', '=', 'jobs.created_by')->where('jobs.created_by',$company_id)->get();

        if(count($jobdetails) > 0){
            return Response::json([
                'status' => true,
                "result" => $jobdetails
            ]);
        }else{
            return Response::json([
                'status' => false,
                "message" => "Job not found"
            ]);
        }
        

    }
}
