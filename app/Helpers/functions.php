<?php

use App\Models\ChooseCategoryLog;
use App\Models\ChoosedCategory;
use App\Models\ChoosedCategoryLog;
use App\Models\SubCategoryType;
use App\Models\User;
use Dwivedianuj9118\PhonePePaymentGateway\PhonePe;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


define('PROJECT_START_DATE', '2024-04-23');
define('TIME_FORMAT', 'Y-m-d H:i:s');
define('PHONEPE_SALTINDEX', 1);



function base64Encode($value) {
    return base64_encode(base64_encode($value));
}

function base64Decode($value) {
    return base64_decode(base64_decode($value));
}

function ajaxResponse($status, $message = '', $data = []) {
    return [
        'data' => $data,
        'status' => $status,
        'message' => $message
    ];
}

function defaultImage() {
    return url('/') . "/assets/img/no-image.jpg";
}



function dbTimezoneToUTC($date) {
    $timezone = Session::get('UserTimeZone');
    if (!$timezone) {
        $timezone = getAdminTimezone();
    }
    return Carbon::createFromFormat(TIME_FORMAT, $date, $timezone)->setTimezone('UTC');
}



function getAdminTimezone() {
    if (Auth::check()) {
      
            return defaultTimeZone();
        
    } else {
        return defaultTimeZone();
    }
}


function defaultTimeZone() {

    return "Asia/kolkata";
}


function convertTimezoneToUTC($date) {
  
        $timezone = defaultTimeZone();
    
    return parseDisplayDateTime(Carbon::createFromFormat(TIME_FORMAT, $date, $timezone)->setTimezone('UTC'));
}

function convertUtcToTimezone($date) {
  
        $timezone = defaultTimeZone();
    
    return parseDisplayDateTime(Carbon::createFromFormat(TIME_FORMAT, $date, 'UTC')->setTimezone($timezone));
    
}


function dbUtcToTimezone($date) {
 
        $timezone = defaultTimeZone();
  
    return Carbon::createFromFormat(TIME_FORMAT, $date, 'UTC')->setTimezone($timezone);
}

function parseDisplayDate($date) {
    return date('m/d/Y', strtotime($date));
}

function parseDisplayDateTime($date) {
    return date('m/d/Y h:i A', strtotime($date));
}

function parseDisplayDateNew($date) {
    return date('d M, Y', strtotime($date));
}

function parseDisplayTime($date) {
    return date('h:i A', strtotime($date));
}

function isDeleted() {
    if (isset(request()->deleted)) {
        return 'Deleted';
    }
    return '';
}
function globalDeleteFileUrl($image) {
    return Storage::disk('public')->delete($image);
}
function uploadBase64($fileName = '', $directory, $prefix, $base64)
{

    list($baseType, $image) = explode(';', $base64);
    list(, $image) = explode(',', $image);
    $image = base64_decode($image);
    $fileName = uniqid($prefix) . '.jpg';
    Storage::disk('public')->put("images/$directory/$fileName", $image, 'public');
    return ["images/$directory/$fileName"];
}



function uploadSingleDoc($doc, $directory, $prefix)
{

    $ext = "jpg";
    $ext = $doc->extension();

    $fileName = uniqid($prefix) . '.' . $ext;


    $filePath = "images/$directory/" . $fileName;

    Storage::disk('public')->put($filePath, file_get_contents($doc), 'public');

    return $filePath;
}


function uploadMultipleDocs($docs, $directory, $prefix)
{
    $filePaths = [];

    foreach ($docs as $doc) {
        $ext = $doc->extension();

        $fileName = uniqid($prefix) . '.' . $ext;

        $filePath = "images/$directory/" . $fileName;

        Storage::disk('public')->put($filePath, file_get_contents($doc), 'public');

        $filePaths[] = $filePath;
    }

    return $filePaths;
}

function globalImageUrl($image){
    return asset('storage/' . $image);
}


function createUserCategory($logId){
    // $sessionData = session()->get('cart', []);
//     if (!empty($sessionData)) {
//         $userId = Auth::id();
// foreach ($sessionData as $typeId => $quantity) {

//     ChoosedCategory::create([
//         'user_id' => $userId,
//         'category_type_id' => $typeId,
//         'quantity' => $quantity,
//     ]);



// }

//     }
$logData = ChoosedCategoryLog::find($logId);

if ($logData) {
    $userId = $logData->user_id;
    $sessionData = json_decode($logData->data, true);

    foreach ($sessionData as $typeId => $quantity) {
        ChoosedCategory::create([
            'user_id' => $userId,
            'category_type_id' => $typeId,
            'quantity' => $quantity,
        ]);
    }
}
    // session()->forget('cart');
}


function createLogUserCategory(){
    $sessionData = session()->get('cart', []);
    $serializedSessionData = json_encode($sessionData);
     $price = 0;
    foreach ($sessionData as $typeId => $quantity) {

            $findPrivePerunit = SubCategoryType::find($typeId);
            $price += $findPrivePerunit->price * $quantity;
            Log::info('entry price', ['amount' => $price]);


        
        
        
        }

    if (!empty($sessionData)) {
        $userId = Auth::user()->id;
   $store =  ChoosedCategoryLog::create([
        'user_id' => $userId,
        'data' => $serializedSessionData,
        'amount' => $price
    ]);
    return $store;





    }
    session()->forget('cart');
}


function initiatePayment($id, $amount){



 $merchentId = env('PHONEPE_MID');
 $saltkey = env('PHONEPE_SALT_KEY');
 $saltIndex = env('PHONEPE_SALT_INDEX');   
$config = new PhonePe($merchentId, $saltkey, $saltIndex);
$merchantTransactionId='MUID' . substr(uniqid(), -6);
$merchantOrderId='Order'.mt_rand(1000,99999);// orderId
$amount = $amount * 100;
$redirectUrl = url('/').'/payment/success'; // Redirect Url after Payment success or fail
$mode="UAT"; // MODE or PAYMENT UAT(test) or PRODUCTION(production)
$callbackUrl= url('/').'/payment/callback';
$mobileNumber = 9455066230;
$metaData = $id;
$data=$config->PaymentCall("$merchantTransactionId","$merchantOrderId","$amount","$redirectUrl","$callbackUrl","$mobileNumber","$mode");

return ['data' => $data, 'txnId' => $merchantTransactionId];
// if($data->)
//header('Location:'. $data['url']);//use when you directly want to redirect to phonepe gateway
return $data['url']; 
}

function filterDateParameters($r) {
    $params['filterStartDate'] = $rStart = $r->filterStartDate ? $r->filterStartDate : PROJECT_START_DATE;
    $params['filterEndDate'] = $rEnd = $r->filterEndDate ? $r->filterEndDate : Carbon::now()->format('Y-m-d');
    $params['label'] = $label = $r->label ? $r->label : 'Lifetime';
    $params['filterStatus'] = $r->filterStatus ? $r->filterStatus : '';


    if ($label == "Today" || $label == "Yesterday" || $label == "Last 7 Days" || $label == "Last 30 Days" || $label == "Custom Range") {
        $start = dbTimezoneToUTC($rStart . " 00:00:00");
        $end = dbTimezoneToUTC($rEnd . " 23:59:59");
    } else {
        $start = $rStart . " 00:00:00";
        $end = $rEnd . " 23:59:59";
    }


    return compact('params', 'start', 'end', 'label');
}

function dateFilter($query, $r, $start, $end) {
    if (isset($r->filterStartDate) && isset($r->filterEndDate) && $r->filterStartDate != '' && $r->filterEndDate != '') {
        $query->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
    }
    return $query;
}