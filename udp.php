<?php
require 'vendor/autoload.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

$client = new GuzzleHttp\Client();
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();


$zk     = new ZKLib(getenv('IP_MESIN_ABSEN'), getenv('PORT_MESIN_ABSEN'));
$conn   = $zk->connect();


$logger = new Logger('udp-service');
// Now add some handlers
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/'.date( "Y-m-d").'.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());


if($conn){
    echo 'starting read data in machine finger print ..'.getenv('IP_MESIN_ABSEN');
    $attendance = $zk->getAttendance();
    sleep(1);
    $counter = 0; 
    while(list($idx, $attendancedata) = each($attendance)):
        ++$counter;
        $badgenum = $attendancedata[0];
        $checktime = urldecode(date( "Y-m-d H:i:s", strtotime( $attendancedata[3] ) ));
        $cabang = getenv('KODE_CABANG');

        $url = getenv('BASE_URL_API').'absensi/'.$badgenum.'/'.$checktime.'/0/'.$cabang;

        $logger->info($url);
        /*
        $res = $client->request('GET', $url);

        $request = new \GuzzleHttp\Psr7\Request('GET',  $url);
        $promise = $client->sendAsync($request)->then(function ($response) {
            echo 'I completed! ' . $response->getBody();
        });
        $promise->wait();
        */
        

    endwhile;
        echo 'success send data '.$counter;
    $zk->disconnect();
} else {
    echo 'connection is failed to '.$ip;
}
