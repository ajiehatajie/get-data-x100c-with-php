<?php

require 'vendor/autoload.php';

use TADPHP\TAD;
use TADPHP\TADFactory;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();


$logger = new Logger('soap-service');
// Now add some handlers
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/'.date( "Y-m-d").'.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());

$tad = (new TADFactory((['ip'=> getenv('IP_MESIN_ABSEN'), 'com_key'=>0])))->get_instance();

echo 'starting read data in machine finger print ..'. getenv('IP_MESIN_ABSEN');
$logs = $tad->get_att_log();
$data = $logs->to_json();

$conv = json_decode($data,true);

foreach ($conv['Row'] as $key) {
    echo $key['PIN'].'<br/>';

    $badgenumber = $key['PIN'];
    $checktime = urldecode(date( "Y-m-d H:i:s", strtotime(  $key['DateTime'] ) ));
    $cabang = getenv('KODE_CABANG');
    echo 'badgenumber is ' .$badgenumber.'<br/>';

    $url = getenv('BASE_URL_API').'absensi/'.$badgenumber.'/'.$checktime.'/0/'.$cabang;
    $logger->info('log',array('url'=>$url));
}
