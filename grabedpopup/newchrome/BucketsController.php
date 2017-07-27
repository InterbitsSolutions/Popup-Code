<?php

namespace App\Http\Controllers;

use App\ConfigAuth;
use App\Models\BucketParams;
use App\Models\User;
use App\DuplicateBuckets;
use App\Models\BucketBrowsers;
use App\Models\BucketFiles;
use App\Models\BucketFolders;
use App\Models\BucketRegions;
use App\Models\BucketShortCodes;
use App\Models\BucketTemplates;
use App\Models\MasterBuckets;
use App\Models\MasterBucketsCounter;
use App\Models\TemplateFiles;
use App\Models\TemplateFolders;
use Aws\DirectoryService\DirectoryServiceClient;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Aws\S3\S3Client;
use App\Classes\S3;
use Mockery\CountValidator\Exception;
use Storage;

use Google\Cloud\Storage\StorageClient;

/*if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJLV6DIJLVNQFOYNA');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

if (!extension_loaded('curl') && !@dl(PHP_SHLIB_SUFFIX == 'so' ? 'curl.so' : 'php_curl.dll'))
	exit("\nERROR: CURL extension not loaded\n\n");

S3::setAuth(awsAccessKey, awsSecretKey);*/

class BucketsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        // Using operation methods creates command implicitly.
        $activeConfigId = $this->getActiveConfig();
        $contents = $s3client->listBuckets();
        foreach($contents['Buckets'] as $content){
            if (preg_match('/www/',$content['Name'])){
                //get bucket first string
                $firstString = substr($content['Name'], 0, strcspn($content['Name'], '1234567890'));
                $replaceCommonString = str_replace(array($firstString,'.com'), '' , $content['Name']);

                //replace first find string from string
                $getUniqueNumber = $this->getNumericVal($replaceCommonString);
                if(!empty($getUniqueNumber)){
                    $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
                    //check if duplicate bucket record exist or not
                    $checkBucketExist = DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->first();
                    if(empty($checkBucketExist)){
                        //add entry in Duplicate bucket
                        $addDuplicateBucket               = new DuplicateBuckets();
                        $addDuplicateBucket->bucket_name  = $content['Name'];
                        $addDuplicateBucket->bucket_code  = $finalString;
                        $addDuplicateBucket->duplicate_bucket_counter  = $getUniqueNumber;
                        $addDuplicateBucket->aws_server_id  = $activeConfigId;
                        $addDuplicateBucket->save();
                    }else{
                        DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $getUniqueNumber]);
                    }
                }
            }
        }
        //create master bucket PID data
        $masterBuckets = MasterBuckets::all();
        $bucketPidArr = array();
        foreach($masterBuckets  as $key => $masterData){
            $bucketPidArr[$masterData['bucket_name']]['id'] = $masterData['id'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_name'] = $masterData['bucket_name'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_pid'] = $masterData['bucket_pid'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_phone_number'] = str_replace(' ', '', $masterData['bucket_phone_number']);
            $bucketPidArr[$masterData['bucket_name']]['ringba_code'] = str_replace(' ', '', $masterData['ringba_code']);
            $bucketPidArr[$masterData['bucket_name']]['bucket_region'] = $masterData['bucket_region'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_short_code'] = $masterData['bucket_short_code'];
            //get bucket params
            $getBucketParams = BucketParams::where('bucket_region', "=", $masterData['bucket_region'])->where('bucket_short_code', '=', $masterData['bucket_short_code'])->first();
            $bucketPidArr[$masterData['bucket_name']]['bucket_parameters'] = (!empty($getBucketParams)) ? $getBucketParams['bucket_parameters'] : '';
        }

        // bucket PARAMS and create master bucket PID data
        $getBucketParams = BucketParams::where('aws_server_id', "=", $activeConfigId)->get();
        $bucketParamArr = array();
        foreach($getBucketParams  as $key => $paramData){
            $key = $paramData['bucket_short_code'][0].$paramData['bucket_region'].$paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_region'] = $paramData['bucket_region'];
            $bucketParamArr[$key]['bucket_short_code'] = $paramData['bucket_short_code'];
            $bucketParamArr[$key]['startString'] = $paramData['bucket_short_code'][0].$paramData['bucket_region'];
            $bucketParamArr[$key]['endString'] = $paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_parameters'] = $paramData['bucket_parameters'];
         }
        //get templates from DB that not to be shown in Buckets
        $templates = DB::table('bucket_templates')->select( DB::raw('group_concat(aws_name) AS template_names'))->first();
        $templateArr = array_filter(explode(',',$templates->template_names));
		//list of all aws server
        $status        =  "Inactive";
        $allAwsServer  =  ConfigAuth::where('status', "=", $status)->get();
		
        return view('adminsOnly.buckets.index', compact('contents', 's3client', 'masterBuckets', 'bucketPidArr', 'templateArr', 'bucketParamArr','allAwsServer'));
    }

    /*
      * function to make bucket duplicate
      * created by BK
      * created on 2nd June'17
      */

    public function duplicator(){
        if(!empty($_POST['duplicate_for']) && !empty($_POST['duplicate_counter'])) {
            //get bucket details
            $bucket = $_POST['duplicate_for'];
            $bucketCounter = $_POST['duplicate_counter'];

            //get bucket Regions
            $regionArr = $this->getRegions();

            //get bucket first string
            $firstString = substr($bucket, 0, strcspn($bucket, '1234567890'));
            $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucket);

            //replace first find string from string
            $getUniqueNumber = $this->getNumericVal($replaceCommonString);
            $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);

            $bucketRegion = 'fr';
            foreach ($regionArr as $regionKey => $regionCodeVal) {
                if (stristr($replaceCommonString, $regionKey) !== FALSE) {
                    $bucketRegion = $regionKey;
                }
            }
            //get region code
            $regionCode = (!empty($bucketRegion)) ? $regionArr[$bucketRegion] : "eu-central-1";
            if(empty($regionCode)){
                //return response
                $return = array(
                    'type' => 'error',
                    'message' => "Region code cannot be empty for $bucketRegion.",
                );
                return json_encode($return);
            }

            //create object for "S3Client"
            $bucketAuthCredentials = $this->getAuthCredentials();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $regionCode,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            // Using operation methods creates command implicitly.
            $bucketArray = $s3client->listBuckets();

            $bucketSuccessResponse = array();
            for($counter = 1; $counter <= $bucketCounter; $counter++ ){
                $activeConfigId = $this->getActiveConfig();
                $checkBucketExist = DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->first();

                $duplicateExist = false;
                if(!empty($checkBucketExist)){
                    $checkBucketExist['duplicate_bucket_counter'] =  $checkBucketExist['duplicate_bucket_counter']+1;
                    $newCounter = ($checkBucketExist['duplicate_bucket_counter']<10) ? '0'.$checkBucketExist['duplicate_bucket_counter'] : $checkBucketExist['duplicate_bucket_counter'];
                    $duplicateExist = true;
                }
                else{
                    //get array of matches string in Bucket name
                    $matchCases =  array();
                    foreach($bucketArray['Buckets'] as $bucketDetail) {
                        if (strpos($bucketDetail['Name'], $finalString) !== false) {
                            $matchCases[] = $bucketDetail;
                        }
                    }
                    $getLastRecord = end($matchCases);

                    $getLastEntry = str_replace(array($firstString,'.com', $finalString), '' , $getLastRecord['Name']);
                    $incrementRecord = $getLastEntry+1;
                    $newCounter = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
                }
                //create next new bucket name
                $newBucketName = $firstString.$newCounter.$finalString.'.com';
                //create string policy for Bucket
                $stringPolicy ='{
                    "Version": "2012-10-17",
                    "Statement": [
                        {
                            "Sid": "Allow Public Access to All Objects",
                            "Effect": "Allow",
                            "Principal": "*",
                            "Action": "s3:GetObject",
                            "Resource": "arn:aws:s3:::'.$newBucketName.'/*"
                        }
                    ]
                }';
                //get list of all buckets and check if bucket name already exist
                $existName = false;
                $contents = $s3client->listBuckets();
                foreach ($contents['Buckets'] as $bucketDetails) {
                    if ($newBucketName == $bucketDetails['Name']) {
                        $existName = true;
                    }
                }
                //if name already exist, then return error message
                if ($existName) {
                    $message = "'$newBucketName' bucket already exist, please try with some other name!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
                else {
                    //check index.html file for existing bucket
                    $existIndex = false;
                    $existingBucket = $s3client->listObjects(array('Bucket' => $bucket));
                    foreach ($existingBucket['Contents'] as $existFiles) {
                        if ($existFiles['Key'] == 'index.html') {
                            $existIndex = true;
                        } else {
                            $existIndex = false;
                        }
                    }
                    //if index file exist, then create bucket
                    if ($existIndex) {
                        try{
                            //trigger exception in a "try" block
                            $result3 = $s3client->createBucket([
                                'Bucket' => $newBucketName,
                            ]);
                            $stp = $s3client->listObjects(array('Bucket' => $bucket));
                            foreach ($stp['Contents'] as $object) {
                                $s3client->copyObject(array(
                                    'Bucket' => $newBucketName,
                                    'Key' => $object['Key'],
                                    'CopySource' => $bucket . '/' . $object['Key']
                                ));
                            }
                            $arg = array(
                                'Bucket' => $newBucketName,
                                'WebsiteConfiguration' => array(
                                    'ErrorDocument' => array('Key' => 'error.html',),
                                    'IndexDocument' => array('Suffix' => 'index.html',),
                                ),
                            );
                            $result2 = $s3client->putBucketWebsite($arg);
                            $result3 = $s3client->putBucketPolicy([
                                'Bucket' => $newBucketName,
                                'Policy' => $stringPolicy,
                            ]);

                            //if already exist, update the counter, else add new entry
                            if($duplicateExist){
                                DuplicateBuckets::where('bucket_code', "=", $finalString)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $newCounter]);
                            }else{
                                //add entry in Duplicate bucket
                                $addDuplicateBucket               = new DuplicateBuckets();
                                $addDuplicateBucket->bucket_name  = $newBucketName;
                                $addDuplicateBucket->bucket_code  = $finalString;
                                $addDuplicateBucket->aws_server_id  = $activeConfigId;
                                $addDuplicateBucket->duplicate_bucket_counter  = $newCounter;
                                $addDuplicateBucket->save();
                            }
                            //get location for new bucket url
                            $location = $s3client->getBucketLocation(array(
                                'Bucket' => $newBucketName
                            ));
                            $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                            $bucketSuccessResponse[] = "$newBucketUrl";
                            //response in case of success if counter match!
                            if($counter==$bucketCounter){
                                $finalMessage =  implode(' , ', $bucketSuccessResponse).' bucket successfully created!';
                                flash($finalMessage);
                                //return response
                                $return = array(
                                    'type' => 'success',
                                    'message' => $bucketSuccessResponse,
                                );
                                return json_encode($return);
                            }
                        }
                        catch(\Exception $exception){
                            $xmlResponse = $exception->getAwsErrorCode();
                            if($xmlResponse=="BucketAlreadyExists"){
                                $message = "Bucket already exists. Please change the URL.";
                            }else{
                                $message = $xmlResponse;
                            }
                            $return = array(
                                'value' => '2',
                                'type' => 'error',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }
                    } else {
                        $message = "Index.html file must be in your existing bucket, please add and try again later!";
                        //return response
                        $return = array(
                            'value' => '100',
                            'type' => 'error',
                            'message' => $message,
                        );
                        return json_encode($return);
                    }
                }
            }
        }
        else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /*
     * function to delete bucket
     * created by BK
     * created on 2nd June'17
     */
    public function deleteBucket(){
        if(!empty($_POST)) {
            $bucketName = $_POST['bucket_name'];
            if(!empty($bucketName)){
                try {
                    $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                    //replace string, get unique number and get final string of the BUCKET
                    $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);

                    //get bucket Regions
                    $regionArr = $this->getRegions();
                    $bucketRegion = 'fr';
                    foreach ($regionArr as $regionKey => $regionCodeVal) {
                        if (stristr($replaceCommonString, $regionKey) !== FALSE) {
                            $bucketRegion = $regionKey;
                        }
                    }
                    //get region code
                    $regionCode = (!empty($bucketRegion)) ? $regionArr[$bucketRegion] : "eu-central-1";
                    if(empty($regionCode)){
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => "Region code cannot be empty for $bucketRegion.",
                        );
                        return json_encode($return);
                    }
                    //create object for "S3Client"
                    $bucketAuthCredentials = $this->getAuthCredentials();
                    $bucketKey = $bucketAuthCredentials['key'];
                    $bucketSecret = $bucketAuthCredentials['secret'];
                    $s3client = new S3Client([
                        'version'     => 'latest',
                        'region'      => $regionCode,
                        'credentials' => [
                            'key'    => $bucketKey,
                            'secret' => $bucketSecret
                        ]
                    ]);
                    $cont = $s3client->getIterator('ListObjects', array('Bucket' => $bucketName));
                    foreach ($cont as $fileDetails){
                        $fileName = $fileDetails['Key'];
                        $result = $s3client->deleteObject(array(
                            'Bucket' => $bucketName,
                            'Key'    => $fileName
                        ));
                    }
                    $s3client->deleteBucket(array(
                        'Bucket' => $bucketName
                    ));

                    $message = "Success ";
                    //return response
                    $return = array(
                        'type' => 'success',
                        'message' => $message,
                    );
                    flash("$bucketName deleted successfully!");
                    return json_encode($return);
                }
                catch(Exception $e){
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $e->getMessage(),
                    );
                    return json_encode($return);
                }
            }else{
                $message = "Bucket name cannot be empty, please check!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }
    /*
     * function to delete bucket in BULK
     * created by BK
     * created on 2nd June'17
     */
    public function deleteMultipleBuckets(){
        if(!empty($_POST)) {
            //get buckets
            $buckets = $_POST['bucket_name'];
            if(!empty($buckets)){
                foreach($buckets as $key => $bucketName){
                    $firstString = substr($bucketName, 0, strcspn($bucketName, '1234567890'));
                    //replace string, get unique number and get final string of the BUCKET
                    $replaceCommonString = str_replace(array($firstString,'.com'), '' , $bucketName);

//                    $getUniqueNumber = $this->getNumericVal($replaceCommonString);

//                    //replace first find string from string
//                    $finalString =  preg_replace("/$getUniqueNumber/", '', $replaceCommonString, 1);
//                    //get region code from - required
//                    $bucketRegion = substr($finalString, 1,2);
//                    $regionCode = BucketRegions::where('region_value', "=", $bucketRegion)->first();
//                    $regionCode = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";
//                    if(empty($regionCode)){
//                        //return response
//                        $return = array(
//                            'type' => 'error',
//                            'message' => "Region code cannot be empty for $bucketRegion.",
//                        );
//                        return json_encode($return);
//                    }

                    //get bucket Regions
                    $regionArr = $this->getRegions();
                    $bucketRegion = 'fr';
                    foreach ($regionArr as $regionKey => $regionCodeVal) {
                        if (stristr($replaceCommonString, $regionKey) !== FALSE) {
                            $bucketRegion = $regionKey;
                        }
                    }
                    //get region code
                    $regionCode = (!empty($bucketRegion)) ? $regionArr[$bucketRegion] : "eu-central-1";
                    if(empty($regionCode)){
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => "Region code cannot be empty for $bucketRegion.",
                        );
                        return json_encode($return);
                    }


                    //create object for "S3Client"
                    $bucketAuthCredentials = $this->getAuthCredentials();
                    $bucketKey = $bucketAuthCredentials['key'];
                    $bucketSecret = $bucketAuthCredentials['secret'];
                    $s3client = new S3Client([
                        'version'     => 'latest',
                        'region'      => $regionCode,
                        'credentials' => [
                            'key'    => $bucketKey,
                            'secret' => $bucketSecret
                        ]
                    ]);
                    $cont = $s3client->getIterator('ListObjects', array('Bucket' => $bucketName));
                    foreach ($cont as $fileDetails){
                        $fileName = $fileDetails['Key'];
                        $result = $s3client->deleteObject(array(
                            'Bucket' => $bucketName,
                            'Key'    => $fileName
                        ));
                    }
                    $s3client->deleteBucket(array(
                        'Bucket' => $bucketName
                    ));
                }
                $message = "Success ";
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                $bucketNames = implode(' , ',$_POST['bucket_name']);
                flash("$bucketNames bucket deleted successfully!");
                return json_encode($return);
            }
            else{
                $message = "Bucket name cannot be empty, please check!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    public function getNumericVal ($str) {
        preg_match_all('/\d+/', $str, $matches);
        return (!empty($matches[0][0])) ? $matches[0][0] : '';
    }

    /*****************TEMPLATE SECTION******************/
    /*
        * function to add Templates
        * created by BK
        * created on 9th June'17
        */
    public function addTemplate()
    {
        $activeConfigId = $this->getActiveConfig();
        if(!empty($_POST)){
            //get params
            $awsName = $_POST['aws_name'];
            $templateName = $_POST['template_name'];
            $templateRegion = $_POST['template_region'];

            //get region code from - required
            $regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
            $templateRegion = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            $checkTemplateExist = BucketTemplates::where('aws_name', "=", $awsName)->first();
            if(empty($checkTemplateExist)) {
                //create string policy for Bucket
                $stringPolicy = '{
                    "Version": "2012-10-17",
                    "Statement": [
                        {
                            "Sid": "Allow Public Access to All Objects",
                            "Effect": "Allow",
                            "Principal": "*",
                            "Action": "s3:GetObject",
                            "Resource": "arn:aws:s3:::' . $awsName . '/*"
                        }
                    ]
                }';
                //create object for "S3Client"
				$primary                = "yes";
				$bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();
                $bucketKey = $bucketAuthCredentials['key'];
                $bucketSecret = $bucketAuthCredentials['secret'];
                $s3client = new S3Client([
                    'version' => 'latest',
                    'region' => $templateRegion,
                    'credentials' => [
                        'key' => $bucketKey,
                        'secret' => $bucketSecret
                    ]
                ]);

                //get list of all buckets and check if bucket name already exist
                $existName = false;
                $contents = $s3client->listBuckets();
//                $contents = array();
//                $contents['Buckets'] = array();
                foreach ($contents['Buckets'] as $bucketDetails) {
                    if ($awsName == $bucketDetails['Name']) {
                        $existName = true;
                    }
                }
                //if name already exist, then return error message
                if ($existName) {
                    $message = "'$awsName' already exist, please try with some other name!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                } else {
                    $result3 = $s3client->createBucket([
                        'Bucket' => $awsName,
                    ]);
                    $arg = array(
                        'Bucket' => $awsName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );
                    $result2 = $s3client->putBucketWebsite($arg);
                    $result3 = $s3client->putBucketPolicy([
                        'Bucket' => $awsName,
                        'Policy' => $stringPolicy,
                    ]);

                    //add bucket in DB
                    $addBucket = new BucketTemplates();
                    $addBucket->aws_server_id = $activeConfigId;
                    $addBucket->template_name = $templateName;
                    $addBucket->template_region = $_POST['template_region'];
                    $addBucket->aws_name = $awsName;
                    $addBucket->save();
                    $insertedId = $addBucket->id;
                    $message = "'$templateName' Template has been added successfully!";

                    flash($message);
                    //unset and create DIR
                    $folderPath = public_path('template_data') . DIRECTORY_SEPARATOR . $insertedId;
                    if (is_dir($folderPath)) {
                        unlink(public_path('template_data') . DIRECTORY_SEPARATOR . $insertedId);
                    }
                    if (mkdir($folderPath, 0777)) {
                        return Redirect::to("upload-template-files/$insertedId");
                    }
                }
            }
            else{
                $message = "Template with '$awsName' already exist in system!";
                flash($message, 'danger');
                return Redirect::to('add-template');
            }
        }else{
			$bucketRegions = BucketRegions::get();
            return view('adminsOnly.buckets.addTemplates', compact('bucketRegions'));
        }
    }

    /*
     * function to delete Templates
     * created by BK
     * created on 19th June
     */
    public function deleteTemplate($templateID)
    {
        //get template data
        $templateData 	= BucketTemplates::where('id', "=", $templateID)->first();
        $templateName 	= $templateData->aws_name;
        $templateRegion = $templateData->template_region;

        //get region code from - required
        $regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
        $regionCode = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

        //create object for "S3Client"
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => $regionCode,
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        try {
            //get list of all buckets and check if bucket name already exist
            $existName = false;
            $contents = $s3client->listBuckets();
            foreach ($contents['Buckets'] as $bucketDetails) {
                if ($templateName == $bucketDetails['Name']) {
                    $existName = true;
                }
            }
            //if name already exist, then return error message
            if ($existName) {
                $cont = $s3client->getIterator('ListObjects', array('Bucket' => $templateName));
                foreach ($cont as $fileName){
                    $fName = $fileName['Key'];
                    $result = $s3client->deleteObject(array(
                        'Bucket' => $templateName,
                        'Key'    => $fName
                    ));
                }
                $s3client->deleteBucket(array(
                    'Bucket' => $templateName
                ));

                $whereArray = array('template_id'=>$templateID);
                //delete template from DB
                BucketTemplates::findOrFail($templateID)->delete();
                //delete files from DB
                TemplateFiles::where($whereArray)->delete();
                //delete folder entries from from DB
                TemplateFolders::where($whereArray)->delete();

                flash("$templateName deleted successfully!");
                return redirect('/list-crm-templates');
            }
            else {
                $whereArray = array('template_id'=>$templateID);
                //delete template from DB
                BucketTemplates::findOrFail($templateID)->delete();
                //delete files from DB
                TemplateFiles::where($whereArray)->delete();
                //delete folder entries from from DB
                TemplateFolders::where($whereArray)->delete();

                flash("$templateName deleted successfully!");
                return redirect('/list-crm-templates');
            }
        } catch (S3Exception $e) {
            $return = array(
                'type' => 'error',
                'message' => $e->getMessage(),
            );
            return redirect('/list-crm-templates');
        }
    }

    /*
     * function to list TEMPLATES
     * created by BK
     * created on 6th June'17
     */
    public function listTemplates()
    {
        $primary                = "yes";
		$bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        // Using operation methods creates command implicitly.
        $contents = $s3client->listBuckets();
        return view('adminsOnly.buckets.viewTemplates', compact('contents', 's3client'));
    }

    /*
     * function to list TEMPLATES
     * created by BK
     * created on 6th June'17
     * change by n.k on 6th july
     * add list of server
     */
    public function listCrmTemplates()
    {
        $primary                = "yes";
        $bucketAuthCredentials  = ConfigAuth::where('primary_network', "=", $primary)->first();		
        $activeConfigId 		=  $bucketAuthCredentials['id'];
        $templates 				= BucketTemplates::where('aws_server_id', "=", $activeConfigId)->get();
		 $status        		=  "Inactive";
		$allAwsServer   		=  ConfigAuth::where('status', "=", $status)->orderBy('id','desc')->get();
        return view('adminsOnly.buckets.viewCrmTemplates', compact('templates','allAwsServer'));
    }

    /*
     * function to manage second step of TEMPLATE - UPLOAD FILES
     * created by BK
     * created on 9th June'17
     */
    public function uploadTemplateFiles($templateId, $folderIN= null)
    {
        if(!empty($folderIN)){
            $getCurrentFolderName = explode('/',$folderIN);
            $folderName = end($getCurrentFolderName);
            //get folder name
            $folderNameDetails = TemplateFolders::where('template_id', "=", $templateId)->where('folder_name', '=', $folderName)->first();
            $folderID = $folderNameDetails['id'];
            $parentFolder = $folderNameDetails['parent_folder'];
            //if in folder, then get files of folder
            $templateFiles = TemplateFiles::where('template_id', "=", $templateId)->where('folder_id', '=', $folderID)->get();
            $templateFolders = TemplateFolders::where('template_id', "=", $templateId)->where('parent_folder', '!=', 0)->where('parent_folder', '=', $folderID)->where('folder_name', '!=', $folderName)->get();
        }else{
            $folderID = '';
            $folderName = '';
            //if at root, then show files and folder for the same
            $templateFiles = TemplateFiles::where('template_id', "=", $templateId)->where('folder_id', '=', 0)->get();
            $templateFolders = TemplateFolders::where('template_id', "=", $templateId)->where('parent_folder', '=', 0)->get();
        }
        return view('adminsOnly.buckets.uploadTemplates', compact('templateId', 'folderIN', 'folderID', 'folderName', 'templateFiles', 'templateFolders'));
    }

    /*****************MASTER BUCKET SECTION******************/
    /*
     * function to list master buckets
     * created by BK
     * created on 6th June'17
     */
    public function listMasterBuckets()
    {
		$totalAwsBuckets = $this->countBuckets();
        $buckets = MasterBuckets::get();

        $buckets = MasterBuckets::join('bucket_templates', 'bucket_templates.id', '=','master_buckets.bucket_template')
                   ->select('master_buckets.id','bucket_name','bucket_region','bucket_short_code','bucket_browser','bucket_template','bucket_phone_number','bucket_pid','bucket_analytics_id', 'template_name', 'ringba_code')->get();
        return view('adminsOnly.buckets.viewMaster', compact('buckets', 'totalAwsBuckets'));
    }

    /*
     * function to add master bucket
     * created by BK
     * created on 5th June'17
     */
    public function addMasterBucket()
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketBrowser = $_POST['bucket_browser'];
            $bucketPhone = $_POST['bucket_phone_number'];
            $bucketPid = $_POST['bucket_pid'];
            $bucketTemplate = $_POST['bucket_template'];
            $bucketAnalyticID = $_POST['bucket_analytics_id'];
            $ringbaCode = $_POST['ringba_code'];

            //get last 4 char of phone number and add in Master Bucket Name
            $trimPhone = str_replace(' ','',$bucketPhone);
            $startPoint =  strlen($trimPhone)- 4;
            $last4Char = substr($trimPhone, $startPoint, 4);

            //create bucket name according to selected fields (ex : afrrpchf0554)
            $bucketName = $bucketShortCode[0].$bucketRegion.$bucketBrowser.$bucketShortCode[1].$last4Char;
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $bucketName)->first();

            //get active config id

            $activeConfigId = $this->getActiveConfig();
            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucket               = new MasterBuckets();
                $addBucket->bucket_name  = $bucketName;
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
                $addBucket->aws_server_id  = $activeConfigId;
                $addBucket->ringba_code  = $ringbaCode;
                $addBucket->save();
                /*
                 * section to create master bucket
                 */
                $insertedId = $addBucket->id;
                $message = "'$bucketName' Bucket has been added successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }else{
                $message = "Bucket with '$bucketName' already exist in system, please select different inputs!";
                flash($message, 'danger');
                return Redirect::to('add-master-bucket');
            }
        }else{
            $activeConfigId = $this->getActiveConfig();
            $bucketRegions = BucketRegions::get();
            $bucketShortCodes = BucketShortCodes::get();
            $bucketBrowsers   = BucketBrowsers::get();
            $bucketTemplates  = BucketTemplates::get();
            return view('adminsOnly.buckets.add', compact('bucketRegions', 'bucketShortCodes', 'bucketBrowsers', 'bucketTemplates'));
        }
    }

    /*
     * function to EDIT master bucket
     * created by BK
     * created on 9th June'17
     */
    public function editMasterBucket($id)
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketBrowser = $_POST['bucket_browser'];
            $bucketPhone = $_POST['bucket_phone_number'];
            $bucketPid = $_POST['bucket_pid'];
            $bucketTemplate = $_POST['bucket_template'];
            $bucketAnalyticID = $_POST['bucket_analytics_id'];
            $ringbaCode = $_POST['ringba_code'];

            //get last 4 char of phone number and add in Master Bucket Name
            $trimPhone = str_replace(' ','',$bucketPhone);
            $startPoint =  strlen($trimPhone)- 4;
            $last4Char = substr($trimPhone, $startPoint, 4);

            //create bucket name according to selected fields (ex : afrrpchf0554)
            $bucketName = $bucketShortCode[0].$bucketRegion.$bucketBrowser.$bucketShortCode[1].$last4Char;

            //get active config id
            $activeConfigId = $this->getActiveConfig();
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $bucketName)->where('aws_server_id', "=", $activeConfigId)->first();

            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucket               = MasterBuckets::find($id);
                $addBucket->bucket_name  = $bucketName;
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
                $addBucket->ringba_code  = $ringbaCode;
                $addBucket->save();
                /*
                 * section to create master bucket
                 */
                $message = "'$bucketName' Bucket updated successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }else{
                //add bucket in DB
                $addBucket               = MasterBuckets::find($id);
                $addBucket->bucket_region  = $bucketRegion;
                $addBucket->bucket_short_code  = $bucketShortCode;
                $addBucket->bucket_browser  = $bucketBrowser;
                $addBucket->bucket_phone_number  = $bucketPhone;
                $addBucket->bucket_pid  = $bucketPid;
                $addBucket->bucket_template  = $bucketTemplate;
                $addBucket->bucket_analytics_id  = $bucketAnalyticID;
                $addBucket->save();
                $message = "'$bucketName' Bucket updated successfully!";
                flash($message);
                return Redirect::to("list-master-buckets");
            }
        }else{
            $currentBucketDetails = MasterBuckets::findOrFail($id);
            $activeConfigId = $this->getActiveConfig();
            $bucketRegions = BucketRegions::get();
            $bucketShortCodes = BucketShortCodes::get();
            $bucketBrowsers   = BucketBrowsers::get();
            $bucketTemplates  = BucketTemplates::get();
            return view('adminsOnly.buckets.edit', compact('currentBucketDetails','bucketRegions', 'bucketShortCodes', 'bucketBrowsers', 'bucketTemplates'));
        }
    }
    /*
     * function to delete master bucket
     * created by BK
     * created on 7th June'17
     */
    public function deleteMasterBucket($bucketID)
    {
        if(!empty($bucketID)){
//            unlink(public_path('bucket_data').DIRECTORY_SEPARATOR.$masterBucketID);
            $whereArray = array('bucket_id'=>$bucketID);
            //delete files from DB
            BucketFiles::where($whereArray)->delete();
            //delete folder entries from from DB
            BucketFolders::where($whereArray)->delete();

            $whereArray = array('id'=>$bucketID);
            MasterBuckets::where($whereArray)->delete();
            flash('Bucket deleted successfully!');
            return redirect('/list-master-buckets');
        }
    }

    /*
    * function to copy master bucket from one to other AWS server
    * created by BK
    * created on 30th June'17
    */
    public function copyMasterBucket(Request $request){
        if(!empty($_POST['aws_server_id']) && !empty($_POST['master_bucket_id'])){
            $masterBucketID = $request->input('master_bucket_id');
            $awsServerID = $request->input('aws_server_id');
            $awsServerName = $request->input('aws_server_name');
            //existing master bucket record
            $existRecord = MasterBuckets::find($masterBucketID);
            $checkBucketExist = MasterBuckets::where('bucket_name', "=", $existRecord->bucket_name)->where('aws_server_id', "=", $awsServerID)->first();

            if(empty($checkBucketExist)){
                $new = $existRecord->replicate();
                $new->aws_server_id = $awsServerID;
                $new->save();
                //return with flash message
                $message = "Master bucket successfully copy to $awsServerName!";
                flash($message);
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                return json_encode($return);
            }else{
                //return with flash message
                $message = "Master bucket already exist in AWS: $awsServerName!";
                $return = array(
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            }
        }else{
            $message = "There is some error in your parameters, please check and try again later!";
            //return response
            $return = array(
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /*
     * function to manage second step of master bucket - UPLOAD FILES
     * created by BK
     * created on 5th June'17
     */
    public function uploadMasterFiles($bucketId, $folderIN= null)
    {
        if(!empty($folderIN)){
            $getCurrentFolderName = explode('/',$folderIN);
            $folderName = end($getCurrentFolderName);

            //get folder name
            $folderNameDetails = BucketFolders::where('bucket_id', "=", $bucketId)->where('folder_name', '=', $folderName)->first();
            $folderID = $folderNameDetails['id'];
            $parentFolder = $folderNameDetails['parent_folder'];

            //if in folder, then get files of folder
            $bucketFiles = BucketFiles::where('bucket_id', "=", $bucketId)->where('folder_id', '=', $folderID)->get();
            $bucketFolders = BucketFolders::where('bucket_id', "=", $bucketId)->where('parent_folder', '!=', 0)->where('parent_folder', '=', $folderID)->where('folder_name', '!=', $folderName)->get();
        }else{
            $folderID = '';
            $folderName = '';
            //if at root, then show files and folder for the same
            $bucketFiles = BucketFiles::where('bucket_id', "=", $bucketId)->where('folder_id', '=', 0)->get();
            $bucketFolders = BucketFolders::where('bucket_id', "=", $bucketId)->where('parent_folder', '=', 0)->get();
        }
        return view('adminsOnly.buckets.upload', compact('bucketId', 'folderIN', 'folderID', 'folderName', 'bucketFiles', 'bucketFolders'));
    }

    /*
    * function to create folder
    * created by BK
    * created on 7th June'17
    */
//    public function addFolder()
//    {
//        if(!empty($_POST['folder_name']) && isset($_POST['parent_folder'])){
//            $templateID = $_POST['template_id'];
//            $folderName = $_POST['folder_name'];
//            $parentFolder = (!empty($_POST['parent_folder'])) ? $_POST['parent_folder'] : 0;
//            $parentFolderName = $_POST['parent_folder_name'];
//
//            $folderPath =  (!empty($parentFolderName)) ? public_path('template_data').DIRECTORY_SEPARATOR.$parentFolderName.DIRECTORY_SEPARATOR.$folderName :
//                public_path('template_data').DIRECTORY_SEPARATOR.$templateID.DIRECTORY_SEPARATOR.$folderName;
//            if(!is_dir($folderPath)){
//                if(mkdir($folderPath, 0777)){
//                    //create structure in folder DB
//                    $addFolder             = new TemplateFolders();
//                    $addFolder->template_id  = $templateID;
//                    $addFolder->folder_name = $folderName;
//                    $addFolder->parent_folder  = $parentFolder;
//                    $addFolder->save();
//                    flash('Folder created successfully!');
//                    $message = "Folder successfully created!";
//                    //return response
//                    $return = array(
//                        'type' => 'success',
//                        'message' => $message,
//                    );
//                    return json_encode($return);
//                }else{
//                    $message = "Please try again later!";
//                    //return response
//                    $return = array(
//                        'type' => 'error',
//                        'message' => $message,
//                    );
//                    return json_encode($return);
//                }
//            }else{
//                $message = "'$folderName' already exist in the directory!";
//                //return response
//                $return = array(
//                    'type' => 'error',
//                    'message' => $message,
//                );
//                return json_encode($return);
//            }
//
//        }
//    }

    /*
    * function to create folder
    * created by BK
    * created on 7th June'17
    */
    public function addFolder()
    {
        if(!empty($_POST['folder_name']) && isset($_POST['parent_folder'])){
            $templateID = $_POST['template_id'];
            $folderName = $_POST['folder_name'];
            $parentFolder = (!empty($_POST['parent_folder'])) ? $_POST['parent_folder'] : 0;
            $parentFolderName = $_POST['parent_folder_name'];

            //get template data
            $templateData = BucketTemplates::where('id', "=", $templateID)->first();
            $templateName = $templateData->aws_name;
            $templateRegion = $templateData->template_region;

            //get region code from - required
            $regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
            $templateRegion = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            //folder path
            $folderPath =  (!empty($parentFolderName)) ? public_path('template_data').DIRECTORY_SEPARATOR.$parentFolderName.DIRECTORY_SEPARATOR.$folderName :
                public_path('template_data').DIRECTORY_SEPARATOR.$templateID.DIRECTORY_SEPARATOR.$folderName;

            //AWS folder path
            $awsFolderPath = (!empty($parentFolderName)) ? $parentFolderName.DIRECTORY_SEPARATOR.$folderName.DIRECTORY_SEPARATOR :$folderName.DIRECTORY_SEPARATOR;

            //create object for "S3Client"
            $primary 				 = "yes";
            $bucketAuthCredentials   = ConfigAuth::where('primary_network', "=", $primary)->first();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $templateRegion,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //add condition try catch
            try {
                // Upload data.
                $result = $s3client->putObject(array(
                    'Bucket'       => $templateName,
                    'Key'          => $awsFolderPath,
                    'SourceFile'   => '/',
                    'ContentType'  => 'text/html',
                    'ACL'          => 'public-read',
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ));
                //check if folder created or not
                if($result['ObjectURL']){
                    if(!is_dir($folderPath)){
                        if(mkdir($folderPath, 0777)){
                            //create structure in folder DB
                            $addFolder             = new TemplateFolders();
                            $addFolder->template_id  = $templateID;
                            $addFolder->folder_name = $folderName;
                            $addFolder->parent_folder  = $parentFolder;
                            $addFolder->save();
                            flash('Folder created successfully!');
                            $message = "Folder successfully created!";
                            //return response
                            $return = array(
                                'type' => 'success',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }else{
                            $message = "Please try again later!";
                            //return response
                            $return = array(
                                'type' => 'error',
                                'message' => $message,
                            );
                            return json_encode($return);
                        }
                    }else{
                        $message = "'$folderName' already exist in the directory!";
                        //return response
                        $return = array(
                            'type' => 'error',
                            'message' => $message,
                        );
                        return json_encode($return);
                    }
                }else{
                    $message = "There is some error while creating directory, please try again later!";
                    //return response
                    $return = array(
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            } catch (S3Exception $e) {
                $return = array(
                    'type' => 'error',
                    'message' => $e->getMessage(),
                );
                return json_encode($return);
            }
        }
    }

    /*
    * function to add files in selected folder
    * created by BK
    * created on 7th June'17
    */
    public function addFiles()
    {
        if(!empty($_POST)){
            $templateID = $_POST['template_id'];
            $parentFolder = (!empty($_POST['parent_folder'])) ? $_POST['parent_folder'] : 0;
            $parentFolderName = $_POST['parent_folder_name'];
            $uploadFilePath = $_POST['upload_file_path'];

            //get template data
            $templateData = BucketTemplates::where('id', "=", $templateID)->first();
            $templateName = $templateData->aws_name;
            $templateRegion = $templateData->template_region;

            //get region code from - required
            $regionCode = BucketRegions::where('region_value', "=", $templateRegion)->first();
            $templateRegion = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            //upload file name
            $uploadFolderPath = public_path('template_data').DIRECTORY_SEPARATOR.$templateID.DIRECTORY_SEPARATOR.$uploadFilePath;

            //create AWS path
            $awsFolderPath = (!empty($_POST['parent_folder'])) ? $uploadFilePath.DIRECTORY_SEPARATOR : '';

            //create object for "S3Client"
			$primary = "yes";
            $bucketAuthCredentials   = ConfigAuth::where('primary_network', "=", $primary)->first();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $templateRegion,
                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);

            foreach ($_FILES["templateFiles"]["error"] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["templateFiles"]["tmp_name"][$key];
                    $name = basename($_FILES["templateFiles"]["name"][$key]);
                    $contentType = $_FILES["templateFiles"]["type"][$key];
                    try {
                        // Upload data.
                        $result = $s3client->putObject(array(
                            'Bucket'       => $templateName,
                            'Key'          => $awsFolderPath.$name,
                            'SourceFile'   => $tmp_name,
                            'ContentType'  => $contentType,
                            'ACL'          => 'public-read',
                            'StorageClass' => 'REDUCED_REDUNDANCY',
                        ));
                        //check if folder created or not
                        if($result['ObjectURL']){
                            if(file_exists("$uploadFolderPath/$name")) {
                                move_uploaded_file($tmp_name, "$uploadFolderPath/$name");
                                flash('Files uploaded successfully!');
                            }else{
                                if(move_uploaded_file($tmp_name, "$uploadFolderPath/$name")){
                                    //create structure in folder DB
                                    $addFiles             = new TemplateFiles();
                                    $addFiles->template_id  = $templateID;
                                    $addFiles->folder_id  = $parentFolder;
                                    $addFiles->file_name  = $_FILES["templateFiles"]["name"][$key];
                                    $addFiles->file_path  = (!empty($uploadFilePath)) ? $uploadFilePath.DIRECTORY_SEPARATOR.$_FILES["templateFiles"]["name"][$key] : $_FILES["templateFiles"]["name"][$key];
                                    $addFiles->save();
                                    flash('Files uploaded successfully!');
                                }
                            }

                        }else{
                            flash('There is some error while uploading, please try again later!', 'danger');
                        }
                    } catch (S3Exception $e) {
                        echo $e->getMessage() . "\n";
                        flash($e->getMessage());
                    }
                }
            }
            $redirectUrl = "upload-template-files/$templateID/$uploadFilePath";
            return Redirect::to($redirectUrl);
        }
    }

    public function duplicateListBuckets()
    {
        $buckets = DuplicateBuckets::all();
        return view('adminsOnly.buckets.view', compact('buckets'));
    }

    /*
     * function to test the link
     * created by BK
     * created on 9th June'17
     */
    public function testLink(){
        $filePath =  public_path('template_data').DIRECTORY_SEPARATOR.'3/businessImage.jpg';
        //create object for "S3Client"
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);

        try {
            // Upload data.
            $result = $s3client->putObject(array(
                'Bucket'       => 'www.frtemplate.com',
                'Key'          => 'images/',
                'SourceFile'   => '/',
                'ContentType'  => 'text/html',
                'ACL'          => 'public-read',
                'StorageClass' => 'REDUCED_REDUNDANCY',
            ));

            //check if folder created or not
            if($result['ObjectURL']){
                die('created');
            }else{
                die('not created');
            }
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }


        die;


        $pid = (!empty($_REQUEST['pid'])) ? $_REQUEST['pid'] : 0;
        return view('adminsOnly.buckets.testLink', compact('pid'));
    }

    /*********************************CREATE CHILD BUCKET FROM MASTER BUCKET**********************/

    /*
    * function to create bucket from master bucket
    * created by BK
    * created on 8th June'17
    */
    public function createChildBucket()
    {
        if(!empty($_POST)){
            //get master bucket details
            $masterBucketID = $_POST['master_bucket'];
            $masterBucketDetails = MasterBuckets::find($masterBucketID);

            //master bucket var
            $masterBucketName = $masterBucketDetails['bucket_name'];
            $bucketRegion = $masterBucketDetails['bucket_region'];
            $bucketShortCode = $masterBucketDetails['bucket_short_code'];
            $bucketBrowser = $masterBucketDetails['bucket_browser'];
            $bucketPhoneNumber = $masterBucketDetails['bucket_phone_number'];
            $bucketPID = $masterBucketDetails['bucket_pid'];

            //get region code from - required
            $regionCode = BucketRegions::where('region_value', "=", $bucketRegion)->first();
            $regionCode = (!empty($regionCode['region_code'])) ? $regionCode['region_code'] : "eu-central-1";

            if(empty($regionCode)){
                //return response
                $return = array(
                    'type' => 'error',
                    'message' => "Region code cannot be empty for $bucketRegion.",
                );
                return json_encode($return);
            }

            //get bucket template details
            $bucketTemplate = $masterBucketDetails['bucket_template'];
            $templateDetails = BucketTemplates::find($bucketTemplate);
            $awsName = $templateDetails['aws_name'];

            //get counter and add on 1
            $childBucketCounter = $masterBucketDetails['child_bucket_counter'];
            $incrementCounter = $childBucketCounter+1;
            $newCounter = ($incrementCounter<10) ? '0'.$incrementCounter : $incrementCounter;

            //add child bucket detail
//            $childBucketName = $newCounter.$masterBucketName;
//            $childBucketLink = "www.support.microsoft$childBucketName.com";

            //set params tp create bucket
            $bucketParams = array();
            $bucketParams['duplicate_for'] = $awsName;
            $bucketParams['region_code'] = $regionCode;
            $bucketParams['bucket_counter'] = $newCounter;
            $bucketParams['bucket_basic_name'] = $masterBucketName;

           // $createBucketResponse = json_decode($this->duplicatorMaster($bucketParams));
             $createBucketResponse = json_decode($this->duplicateUsingMasterTemplate($bucketParams));

            if($createBucketResponse->type=='success'){
                $updatedCounter = $createBucketResponse->bucket_updated_counter;
                $childBucketName = $createBucketResponse->bucket_url;
				$serverName         = $createBucketResponse->bucket_created_server_name;
                //update counter in master bucket table
                MasterBuckets::where('id', $masterBucketID)->update(['child_bucket_counter' => $updatedCounter]);

                $activeConfigId = $this->getActiveConfig();
                DuplicateBuckets::where('bucket_code', $masterBucketName)->where('aws_server_id', "=", $activeConfigId)->update(['duplicate_bucket_counter' => $updatedCounter]);

                $message = "$childBucketName bucket has been added successfully on Sever : $serverName !";
                flash($message);
                //return response
                $return = array(
                    'type' => 'success',
                    'message' => $message,
                );
                return json_encode($return);
            }else{
                return json_encode($createBucketResponse);
            }
        }
    }

    /*
    * function to make bucket duplicate
    * created by BK
    * created on 2nd June'17
    */

    public function duplicatorMaster($bucketParams){
        if(!empty($bucketParams)) {
            //bucket params
            $bucket = $bucketParams['duplicate_for'];
            $bucketCounter = $bucketParams['bucket_counter'];
            $bucketRegionCode = $bucketParams['region_code'];
            $bucketBasicName = $bucketParams['bucket_basic_name'];

            //create object for "S3Client"
            $bucketAuthCredentials = $this->getAuthCredentials();
            $bucketKey = $bucketAuthCredentials['key'];
            $bucketSecret = $bucketAuthCredentials['secret'];
            $s3client = new S3Client([
                'version'     => 'latest',
                'region'      => $bucketRegionCode,

                'credentials' => [
                    'key'    => $bucketKey,
                    'secret' => $bucketSecret
                ]
            ]);
            //get list of all buckets and check if bucket name already exist
            $existName = false;
            $contents = $s3client->listBuckets();

            //get array of matches string in Bucket name
            $matchCases =  array();
            foreach($contents['Buckets'] as $bucketDetail) {
                if (strpos($bucketDetail['Name'], $bucketBasicName) !== false) {
                    $matchCases[] = $bucketDetail;
                }
            }

            //get last bucket counter
            if(!empty($matchCases)){
                $getLastRecord = end($matchCases);
                $firstString = substr($getLastRecord['Name'], 0, strcspn($getLastRecord['Name'], '1234567890'));
                $getLastEntry = str_replace(array($firstString,'.com', $bucketBasicName), '' , $getLastRecord['Name']);
                $incrementRecord = $getLastEntry+1;
                $newCounter = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
            }else{
                $firstString = 'www.support.microsoft';
                $bucketCounter = $this->getConfigCounter();
                $newCounter = ($bucketCounter<10) ? '0'.$bucketCounter : $bucketCounter;
            }

            //create final bucket name
            $childBucketName = $newCounter.$bucketBasicName;
            $newBucketName = "$firstString$childBucketName.com";

            $stringPolicy ='{
                "Version": "2012-10-17",
                "Statement": [
                    {
                        "Sid": "Allow Public Access to All Objects",
                        "Effect": "Allow",
                        "Principal": "*",
                        "Action": "s3:GetObject",
                        "Resource": "arn:aws:s3:::'.$newBucketName.'/*"
                    }
                ]
            }';

            foreach ($contents['Buckets'] as $bucketDetails) {
                if ($newBucketName == $bucketDetails['Name']) {
                    $existName = true;
                }
            }
            //if name already exist, then return error message
            if ($existName) {
                $message = "'$newBucketName' bucket already exist, please try with some other name!";
                //return response
                $return = array(
                    'value' => '100',
                    'type' => 'error',
                    'message' => $message,
                );
                return json_encode($return);
            } else {
                //check index.html file for existing bucket
                $existIndex = false;
                $existingBucket = $s3client->listObjects(array('Bucket' => $bucket));
                foreach ($existingBucket['Contents'] as $existFiles) {
                    if ($existFiles['Key'] == 'index.html') {
                        $existIndex = true;
                    } else {
                        $existIndex = false;
                    }
                }
                //if index file exist, then create bucket
                if ($existIndex) {

                    $result3 = $s3client->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    $stp = $s3client->listObjects(array('Bucket' => $bucket));
                    foreach ($stp['Contents'] as $object) {
                        $s3client->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );
                    $result2 = $s3client->putBucketWebsite($arg);
                    $result3 = $s3client->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    $location = $s3client->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    //return response
                    $return = array(
                        'type' => 'success',
                        'bucket_url' => $newBucketUrl,
                        'bucket_updated_counter' => $newCounter,
                    );
                    return json_encode($return);

                } else {
                    $message = "Index.html file must be in your selected Template of Master bucket, please add and try again later!";
                    //return response
                    $return = array(
                        'value' => '100',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);
                }
            }
        }else{
            $message = "There is some error in the params posted by you, please check!";
            //return response
            $return = array(
                'value' => '100',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
    }

    /**************************MULTIPLE BUCKET SECTION*************************/
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function multipleBuckets()
    {
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        // Using operation methods creates command implicitly.
        $contents = $s3client->listBuckets();
        $masterBuckets = MasterBuckets::all();

        //create master bucket PID data
        $bucketPidArr = array();
        foreach($masterBuckets  as $key => $masterData){
            $bucketPidArr[$masterData['bucket_name']]['id'] = $masterData['id'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_name'] = $masterData['bucket_name'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_pid'] = $masterData['bucket_pid'];
        }
        return view('adminsOnly.buckets.multipleIndex', compact('contents', 's3client', 'masterBuckets', 'bucketPidArr'));
    }

    /*
     * function to get the region array
     * created by BK
     * created on 27th June
     * return : array
     */
    public function getRegions(){
        $bucketRegions = BucketRegions::all();
        $regionArr = array();
        foreach($bucketRegions as $regions){
            $regionArr[$regions->region_value] = $regions->region_code;
        }
        return $regionArr;
    }

    /*
     * function to manage Bucket params
     * created by BK
     * created on 28th June'17
     */
    public function addBucketParams()
    {
        $activeConfigId = $this->getActiveConfig();
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketParameters = $_POST['bucket_parameters'];
            //check if record exist or not
            $checkBucketParam = BucketParams::where('bucket_region', "=", $bucketRegion)->where('bucket_short_code', "=", $bucketShortCode)->first();
            if(empty($checkBucketExist)){
                //add bucket in DB
                $addBucketParam               = new BucketParams();
                $addBucketParam->bucket_region  = $bucketRegion;
                $addBucketParam->bucket_short_code  = $bucketShortCode;
                $addBucketParam->bucket_parameters  = $bucketParameters;
                $addBucketParam->aws_server_id  = $activeConfigId;
                $addBucketParam->save();
                /*
                 * section to create master bucket
                 */
                $insertedId = $addBucketParam->id;
                $message = "Bucket Parameters has been added successfully!";
                flash($message);
                return Redirect::to("list-bucket-params");
            }else{
                $message = "Bucket already exist in system, please select different inputs!";
                flash($message, 'danger');
                return Redirect::to('add-bucket-params');
            }
        }else{
            $bucketRegions = BucketRegions::where('aws_server_id', "=", $activeConfigId)->get();
            $bucketShortCodes = BucketShortCodes::where('aws_server_id', "=", $activeConfigId)->get();
            return view('adminsOnly.buckets.addParams', compact('bucketRegions', 'bucketShortCodes'));
        }
    }

    /*
    * function to list bucket params
    * created by BK
    * created on 28th June'17
    */
    public function listBucketParams()
    {
        $activeConfigId = $this->getActiveConfig();
        $bucketParams = BucketParams::where('aws_server_id', "=", $activeConfigId)->get();
        return view('adminsOnly.buckets.viewParams', compact('bucketParams'));
    }

    /*
    * function to edit bucket params
    * created by BK
    * created on 28th June'17
    */
    public function editBucketParams($id)
    {
        if(!empty($_POST)){
            $bucketRegion = $_POST['bucket_region'];
            $bucketShortCode = $_POST['bucket_short_code'];
            $bucketParameters = $_POST['bucket_parameters'];
            //add bucket in DB
            $addBucketParam = BucketParams::find($id);
            $addBucketParam->bucket_region  = $bucketRegion;
            $addBucketParam->bucket_short_code  = $bucketShortCode;
            $addBucketParam->bucket_parameters  = $bucketParameters;
            $addBucketParam->save();
            $message = "Bucket Parameters updated successfully!";
            flash($message);
            return Redirect::to("list-bucket-params");
        }
        $bucketParams = BucketParams::findOrFail($id);
        $bucketRegions = BucketRegions::all();
        $bucketShortCodes = BucketShortCodes::all();
        return view('adminsOnly.buckets.editParams', compact('bucketParams', 'bucketRegions', 'bucketShortCodes'));
    }
    /*
     * function to delete bucket params
     * created by BK
     * created on 28th June'17
     */
    public function deleteBucketParams($bucketID)
    {
        if(!empty($bucketID)){
            $whereArray = array('id'=>$bucketID);
            //delete files from DB
            BucketParams::where($whereArray)->delete();
            flash('Bucket params deleted successfully!');
            return Redirect::to("list-bucket-params");
        }
    }
			
	
	/*
	* function to make bucket duplicate
	* created by NK
	* created on 30 June'17
	*/
	public function duplicateToAws()
	{
		$duplciateFrom          = Input::get('duplicate_for');
		$newBucketName          = Input::get('new_bucket_name');
		$region                 = Input::get('duplicateToAwsRegion');
		$status                 = "Active";
		$awsServerActive        = ConfigAuth::where('status', "=", $status)->first();
		$activeServerKey        = $awsServerActive['key'];
		$actvieServerSecretKey  = $awsServerActive['secret'];
		$copyToServerId         = Input::get('aws_server_id');
		$allAwsServer           = ConfigAuth::where('id', "=", $copyToServerId)->first();
		$toServerKey            = $allAwsServer['key'];
		$toServerSecretKey      = $allAwsServer['secret'];
		$toServerName           = $allAwsServer['aws_name'];
		$bucket 		        = $duplciateFrom;
		//create object for "S3Client"
		$s3clientActive               = new S3Client([
			'version'     => 'latest',
			'region'      => $region,
			'credentials' => [
				'key'    => $activeServerKey,
				'secret' => $actvieServerSecretKey
			]
		]);
		$s3clientToMove               = new S3Client([
			'version'     => 'latest',
			'region'      => $region,
			'credentials' => [
				'key'    => $toServerKey,
				'secret' => $toServerSecretKey
			]
		]);
		if($newBucketName=="")
		{
			$newBucketName = $duplciateFrom;
		}
		//create string policy for Bucket
		 $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';
					
		//get list of all buckets and check if bucket name already exist
		$existName = false;
		$contents = $s3clientToMove->listBuckets();
		foreach ($contents['Buckets'] as $bucketDetails) {
			if ($newBucketName == $bucketDetails['Name']) {
				$existName = true;
			}
		}

		//if name already exist, then return error message
		if ($existName) {
			$message = "'$newBucketName' bucket already exist, please try with some other name!";
			//return response
			$return = array(
				'value' => '2',
				'type' => 'error',
				'message' => $message,
			);
			return json_encode($return);
		}
		else {
			//check index.html file for existing bucket
			$existIndex = false;
			$existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
			foreach ($existingBucket['Contents'] as $existFiles) {
				if ($existFiles['Key'] == 'index.html') {
					$existIndex = true;
				} else {
					$existIndex = false;
				}
			}
			//if index file exist, then create bucket
			if($existIndex)
			{
				try{
					//create instance of NEW server, where we have to move/copy
					$result3 = $s3clientToMove->createBucket([
						'Bucket' => $newBucketName,
					]);
					//list the current bucket from active AWS server, from where we have to move/copy
					$stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to
					
					foreach ($stp['Contents'] as $object) {
						//create instance of NEW server, where we have to move/copy
						$s3clientToMove->copyObject(array(
							'Bucket' => $newBucketName,
							'Key' => $object['Key'],
							'CopySource' => $bucket . '/' . $object['Key']
						));
					}
					$arg = array(
						'Bucket' => $newBucketName,
						'WebsiteConfiguration' => array(
							'ErrorDocument' => array('Key' => 'error.html',),
							'IndexDocument' => array('Suffix' => 'index.html',),
						),
					);
				
					//create instance of NEW server, where we have to move/copy
					$result2 = $s3clientToMove->putBucketWebsite($arg);
					$result3 = $s3clientToMove->putBucketPolicy([
						'Bucket' => $newBucketName,
						'Policy' => $stringPolicy,
					]);
					
					//get location for new bucket url
					//create instance of NEW server, where we have to move/copy
					$location = $s3clientToMove->getBucketLocation(array(
						'Bucket' => $newBucketName
					));
					$newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
					//response in case of success if counter match!
					$finalMessage =  $newBucketUrl.' bucket successfully created on new server'.$toServerName;
					flash($finalMessage);
					//return response
					$return = array(
						'value' => '1',
						'type' => 'success',
						'message' => $finalMessage,
						'b_url' => $newBucketUrl,
						'b_name'=>$newBucketName,
					);
					return json_encode($return);					
				}
				catch(\Exception $exception){
					
					$xmlResposne = $exception->getAwsErrorCode();
					if($xmlResposne=="BucketAlreadyExists")
					{
						$message = "Bucket already exists. Please change the URL.";						
					}
					else
					{
						$message = $xmlResposne;
					}
					$return = array(
						'value' => '2',
						'type' => 'error',
						'message' => $message,
					);
					return json_encode($return);	
					
				}
				
			}
		}
	}


    /**
     * Display a listing of the resource.
     * TEST BUCKETS
     * @return \Illuminate\Http\Response
     */
    public function testBuckets()
    {
        $bucketAuthCredentials = $this->getAuthCredentials();
        $bucketKey = $bucketAuthCredentials['key'];
        $bucketSecret = $bucketAuthCredentials['secret'];
        $s3client = new S3Client([
            'version'     => 'latest',
            'region'      => 'eu-central-1',
            'credentials' => [
                'key'    => $bucketKey,
                'secret' => $bucketSecret
            ]
        ]);
        $params = [
            'Bucket' => 'foo',
            'Key'    => 'baz',
            'Body'   => 'bar'
        ];
        $contents = $s3client->listBuckets();
        //create master bucket PID data
        $masterBuckets = MasterBuckets::all();
        $bucketPidArr = array();
        foreach($masterBuckets  as $key => $masterData){
            $bucketPidArr[$masterData['bucket_name']]['id'] = $masterData['id'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_name'] = $masterData['bucket_name'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_pid'] = $masterData['bucket_pid'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_region'] = $masterData['bucket_region'];
            $bucketPidArr[$masterData['bucket_name']]['bucket_short_code'] = $masterData['bucket_short_code'];
            //get bucket params
            $getBucketParams = BucketParams::where('bucket_region', "=", $masterData['bucket_region'])->where('bucket_short_code', '=', $masterData['bucket_short_code'])->first();
            $bucketPidArr[$masterData['bucket_name']]['bucket_parameters'] = (!empty($getBucketParams)) ? $getBucketParams['bucket_parameters'] : '';
        }
        // bucket PARAMS
        $getBucketParams = BucketParams::all();
        //create master bucket PID data
        $bucketParamArr = array();
        foreach($getBucketParams  as $key => $paramData){
            $key = $paramData['bucket_short_code'][0].$paramData['bucket_region'].$paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_region'] = $paramData['bucket_region'];
            $bucketParamArr[$key]['bucket_short_code'] = $paramData['bucket_short_code'];
            $bucketParamArr[$key]['startString'] = $paramData['bucket_short_code'][0].$paramData['bucket_region'];
            $bucketParamArr[$key]['endString'] = $paramData['bucket_short_code'][1];
            $bucketParamArr[$key]['bucket_parameters'] = $paramData['bucket_parameters'];
        }
        //get templates from DB that not to be shown in Buckets
        $templates = DB::table('bucket_templates')->select( DB::raw('group_concat(aws_name) AS template_names'))->first();
        $templateArr = array_filter(explode(',',$templates->template_names));
        //list of all aws server
        $status        =  "Inactive";
        $allAwsServer  =  ConfigAuth::where('status', "=", $status)->get();
        return view('adminsOnly.buckets.testBuckets', compact('contents', 's3client', 'masterBuckets', 'bucketPidArr', 'templateArr', 'bucketParamArr','allAwsServer'));
    }
	
	
	 public function googleBuckets() {
		
		/*$disk = Storage::disk('gcs');
		$url = $disk->url('assests/xe-ie.png');*/
		
		//putenv('GOOGLE_APPLICATION_CREDENTIALS=/crmstaging/my-service2.json');
		
		$storage = new StorageClient([
    'projectId' => 'original-folio-171317'
]);
		
		//$storage->setAuthConfig('/crmstaging/my-service2.json');
		
		$bucket = $storage->createBucket('aurobucket');
		
		$url = "Good";
		
		 return view('adminsOnly.buckets.googleBuckets', compact('contents', 'url'));
		 
		 
	 }
	 
	 
	 
	 
	 public function recurse_copy($src,$dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    /*
	* function to make tempalte duplicate
	* created by NK
	* created on 6 July'17
	*/
    public function moveTemplateToNewAws()
    {
        $duplciateFrom                  = Input::get('duplicate_for');
        $newBucketName                  = Input::get('new_bucket_name');
        $region                         = Input::get('duplicateToAwsRegion');
        $template_id                    = Input::get('template_id');
        $status                         = "Active";
        $awsServerActive                = ConfigAuth::where('status', "=", $status)->first();
        $activeServerKey                = $awsServerActive['key'];
        $actvieServerSecretKey          = $awsServerActive['secret'];
        $copyToServerId                 = Input::get('aws_server_id');
        $allAwsServer                   = ConfigAuth::where('id', "=", $copyToServerId)->first();
        $toServerKey                    = $allAwsServer['key'];
        $toServerSecretKey              = $allAwsServer['secret'];
        $toServerName                   = $allAwsServer['aws_name'];
        $bucket 		                = $duplciateFrom;

        //create object for "S3Client"
        $s3clientActive               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $activeServerKey,
                'secret' => $actvieServerSecretKey
            ]
        ]);
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);
        if($newBucketName=="")
        {
            $newBucketName = $duplciateFrom;
        }
        //create string policy for Bucket
        $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';

        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3clientToMove->listBuckets();
        foreach ($contents['Buckets'] as $bucketDetails) {
            if ($newBucketName == $bucketDetails['Name']) {
                $existName = true;
            }
        }

        //if name already exist, then return error message
        if ($existName) {
            $message = "'$newBucketName' bucket already exist, please try with some other name!";
            //return response
            $return = array(
                'value' => '2',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
        else {
            //check index.html file for existing bucket
            $existIndex = false;
            $existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
            foreach ($existingBucket['Contents'] as $existFiles) {
                if ($existFiles['Key'] == 'index.html') {
                    $existIndex = true;
                } else {
                    $existIndex = false;
                }
            }
            //if index file exist, then create bucket
            if($existIndex)
            {
                try{
                    //create instance of NEW server, where we have to move/copy
                    $result3 = $s3clientToMove->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    //list the current bucket from active AWS server, from where we have to move/copy
                    $stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to

                    foreach ($stp['Contents'] as $object) {
                        //create instance of NEW server, where we have to move/copy
                        $s3clientToMove->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );

                    //create instance of NEW server, where we have to move/copy
                    $result2 = $s3clientToMove->putBucketWebsite($arg);
                    $result3 = $s3clientToMove->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    //create instance of NEW server, where we have to move/copy
                    $location = $s3clientToMove->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";
                    //response in case of success if counter match!
                    $finalMessage =  $newBucketUrl.' template successfully created on new server'.$toServerName;

                    /* new */
                    $templateData                   = BucketTemplates::where('id', "=", $template_id)->first();
                    $templateName                   = $templateData['template_name'];
                    $templateRegion                 = $templateData['template_region'];
                    $templateObj                    = new BucketTemplates();
                    $templateObj->aws_server_id     = $copyToServerId;
                    $templateObj->template_name     = $templateName;
                    $templateObj->template_region   = $templateRegion;
                    $templateObj->aws_name          = $newBucketName;
                    $templateObj->save();
                    $insertedTemplateId             = $templateObj->id;
                    $folderDataCount                = TemplateFolders::where('id',$template_id)->count();
                    if($folderDataCount>0)
                    {
                        $folderData                     = TemplateFolders::where('id', $template_id)->get();
                        foreach ($folderData as $folderVal)
                        {
                            $addFolder = new TemplateFolders();
                            $addFolder->template_id     = $insertedTemplateId;
                            $addFolder->folder_name     = $folderVal->folder_name;
                            $addFolder->parent_folder   = $folderVal->parent_folder;
                            $addFolder->save();
                            $folderId  = $addFolder->id;
                            $templateFilesData          = TemplateFiles::where('folder_id',$folderId)->get();
                            foreach($templateFilesData as $templateFilesVal)
                            {
                                $addFiles               = new TemplateFiles();
                                $addFiles->template_id  = $insertedTemplateId;
                                $addFiles->folder_id    = $addFolder->id;
                                $addFiles->file_name    = $templateFilesVal->id;
                                $addFiles->file_path    = $templateFilesVal->id;
                                $addFiles->save();
                            }
                        }

                    }

                    $templateFilesDataCount    = TemplateFiles::where('id',$template_id)->where('folder_id',0)->count();
                    if($templateFilesDataCount>0)
                    {
                        $templateFilesData    = TemplateFiles::where('id',$template_id)->where('folder_id',0)->count();
                        foreach ($templateFilesData as $templateFilesVal)
                        {
                            $addFiles = new TemplateFiles();
                            $addFiles->template_id = $insertedTemplateId;
                            $addFiles->folder_id = 0;
                            $addFiles->file_name = $templateFilesVal->id;
                            $addFiles->file_path = $templateFilesVal->id;
                            $addFiles->save();
                        }
                    }

                    $srcPath = public_path('template_data').DIRECTORY_SEPARATOR.$template_id;
                    $destPath = public_path('template_data').DIRECTORY_SEPARATOR.$insertedTemplateId;
                    $this->recurse_copy($srcPath,$destPath);
                    /* new */




                    flash($finalMessage);
                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'message' => $finalMessage,
                        'b_url' => $newBucketUrl,
                        'b_name'=>$newBucketName,
                    );
                    return json_encode($return);
                }
                catch(\Exception $exception){

                    $xmlResposne = $exception->getAwsErrorCode();
                    if($xmlResposne=="BucketAlreadyExists")
                    {
                        $message = "Bucket already exists. Please change the URL.";
                    }
                    else
                    {
                        $message = $xmlResposne;
                    }
                    $return = array(
                        'value' => '2',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);

                }

            }
        }
    }
	
	
	
    /*
	* function to make bucket duplicate
	* created by NK
	* created on 11 July'17
    * $s3clientActive means from where we copy the master template robert
    * $s3clientToMove is the object of active server
	*/
    public function duplicateUsingMasterTemplate($bucketParams)
    {
        $duplciateFrom          = $bucketParams['duplicate_for'];
        $region                 = $bucketParams['region_code'];
        $bucket_counter         = $bucketParams['bucket_counter'];
        $bucketBasicName        = $bucketParams['bucket_basic_name'];
        $primary                = "yes";
        $status                 = "active";
        //$awsServerActive       = ConfigAuth::where('status', "=", $status)->first();
        $awsServerActive        = ConfigAuth::where('primary_network', "=", $primary)->first();
        $activeServerKey        = $awsServerActive['key'];
        $actvieServerSecretKey  = $awsServerActive['secret'];
        $allAwsServer           = ConfigAuth::where('status', "=", $status)->first();
        $toServerKey            = $allAwsServer['key'];
        $toServerSecretKey      = $allAwsServer['secret'];
        $toServerName           = $allAwsServer['aws_name'];
        $bucket 		        = $duplciateFrom;
        //create object for "S3Client"
        $s3clientActive               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $activeServerKey,
                'secret' => $actvieServerSecretKey
            ]
        ]);
        $s3clientToMove               = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $toServerKey,
                'secret' => $toServerSecretKey
            ]
        ]);

        /* code to final the bucekt name */
        $contents = $s3clientToMove->listBuckets();
        //get array of matches string in Bucket name
        $matchCases =  array();
        foreach($contents['Buckets'] as $bucketDetail) {
            if (strpos($bucketDetail['Name'], $bucketBasicName) !== false) {
                $matchCases[] = $bucketDetail;
            }
        }
        //get last bucket counter
        if(!empty($matchCases)){
            $getLastRecord      = end($matchCases);
            $firstString        = substr($getLastRecord['Name'], 0, strcspn($getLastRecord['Name'], '1234567890'));
            $getLastEntry       = str_replace(array($firstString,'.com', $bucketBasicName), '' , $getLastRecord['Name']);
            $incrementRecord    = $getLastEntry+1;
            $newCounter         = ($incrementRecord<10) ? '0'.$incrementRecord : $incrementRecord;
        }else{
            $firstString        = 'www.support.microsoft';
            $bucketCounter      = $this->getConfigCounter();
            $newCounter         = ($bucketCounter<10) ? '0'.$bucketCounter : $bucketCounter;
        }
        //create final bucket name
        $childBucketName = $newCounter.$bucketBasicName;
        $newBucketName = "$firstString$childBucketName.com";
        /* code to final the bucket name */
        if($newBucketName=="")
        {
            $newBucketName = $duplciateFrom;
        }
       //create string policy for Bucket
        $stringPolicy ='{
					"Version": "2012-10-17",
					"Statement": [
						{
							"Sid": "Allow Public Access to All Objects",
							"Effect": "Allow",
							"Principal": "*",
							"Action": "s3:GetObject",
							"Resource": "arn:aws:s3:::'.$newBucketName.'/*"
						}
					]
				}';

        //get list of all buckets and check if bucket name already exist
        $existName = false;
        $contents = $s3clientToMove->listBuckets();
        foreach ($contents['Buckets'] as $bucketDetails) {
            if ($newBucketName == $bucketDetails['Name']) {
                $existName = true;
            }
        }
        //if name already exist, then return error message
        if ($existName) {
            $message = "'$newBucketName' bucket already exist, please try with some other name!";
            //return response
            $return = array(
                'value' => '2',
                'type' => 'error',
                'message' => $message,
            );
            return json_encode($return);
        }
        else {
            //check index.html file for existing bucket
            $existIndex = false;
            $existingBucket = $s3clientActive->listObjects(array('Bucket' => $bucket));
            foreach ($existingBucket['Contents'] as $existFiles) {
                if ($existFiles['Key'] == 'index.html') {
                    $existIndex = true;
                } else {
                    $existIndex = false;
                }
            }
            //if index file exist, then create bucket
            if($existIndex)
            {
                try{
                    //create instance of NEW server, where we have to move/copy
                    $result3 = $s3clientToMove->createBucket([
                        'Bucket' => $newBucketName,
                    ]);
                    //list the current bucket from active AWS server, from where we have to move/copy
                    $stp = $s3clientActive->listObjects(array('Bucket' => $bucket)); // to

                    foreach ($stp['Contents'] as $object) {
                        //create instance of NEW server, where we have to move/copy
                        $s3clientToMove->copyObject(array(
                            'Bucket' => $newBucketName,
                            'Key' => $object['Key'],
                            'CopySource' => $bucket . '/' . $object['Key']
                        ));
                    }
                    $arg = array(
                        'Bucket' => $newBucketName,
                        'WebsiteConfiguration' => array(
                            'ErrorDocument' => array('Key' => 'error.html',),
                            'IndexDocument' => array('Suffix' => 'index.html',),
                        ),
                    );

                    //create instance of NEW server, where we have to move/copy
                    $result2 = $s3clientToMove->putBucketWebsite($arg);
                    $result3 = $s3clientToMove->putBucketPolicy([
                        'Bucket' => $newBucketName,
                        'Policy' => $stringPolicy,
                    ]);

                    //get location for new bucket url
                    //create instance of NEW server, where we have to move/copy
                    $location = $s3clientToMove->getBucketLocation(array(
                        'Bucket' => $newBucketName
                    ));
                    $newBucketUrl = "http://".$newBucketName.".s3-website.".$location['LocationConstraint'].".amazonaws.com";

                    //return response
                    $return = array(
                        'value' => '1',
                        'type' => 'success',
                        'bucket_url' => $newBucketUrl,
                        'bucket_updated_counter' => $newCounter,
                        'bucket_created_server_name' => $toServerName,
                    );
                    return json_encode($return);
                }
                catch(\Exception $exception){

                    $xmlResposne = $exception->getAwsErrorCode();
                    if($xmlResposne=="BucketAlreadyExists")
                    {
                        $message = "Bucket already exists. Please change the URL.";
                    }
                    else
                    {
                        $message = $xmlResposne;
                    }
                    $return = array(
                        'value' => '2',
                        'type' => 'error',
                        'message' => $message,
                    );
                    return json_encode($return);

                }

            }
        }
    }

	
	
	
	
}
