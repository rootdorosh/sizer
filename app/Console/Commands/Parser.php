<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Parser as ParserModel;
use App\Services\Curl;

class Parser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->process();
    }
    
    function process()
    {
        $data = file_get_contents('http://filefab.com/api.php?l=Nmlu9vQZ2E709_R32IXr7S3-4nGwdzAd4sc_jgf9sho');
        $proxies = explode("\n", str_replace(['<pre>', '</pre>'], '', $data));
        unset($proxies[count($proxies)-1]);
        
        ParserModel::whereNull('response')->chunk(2, function ($parsers) use ($proxies) {
            foreach ($parsers as $parser) {
                echo $parser->id . "\n";
                
                $headers = [];
                $posts = json_decode($parser->request, true);
                
                $posts['preferences'] = [
                    'fit_preference' => 'regular',
                    'length' => 'regular',
                    'neck_preference' => 'regular',
                    'neck_preference' => 'regular',
                    'sleeve_length' => 'regular',
                ];
                
                $proxy = $proxies[array_rand($proxies)];
                
                //echo $proxy . "\n";
                
                $page = (new Curl('https://www.sonofatailor.com/calcufit_api/calculate-fit/', $headers, $posts))
                    ->setOpt('isPostPayload', true)
                    //->setOpt('proxy', $proxy)
                    ->init()
                    ->execute()
                    ->close();
                
                $parser->response = $page->response;
                $parser->save();
                
                sleep(1);
            }
            
        });        
    }
    
    function generate()
    {
        $size_types = [
            2,
        ];

        $foot_lengths = [
            '25'  => '150-182',
            '25.5' => '151-184',
            '26' => '153-186',
            '27' => '157-190',
            '27.5' => '159-192',
            '28' => '161-195',
            '29' => '166-200',
            '29.5' => '169-202',
            '30' => '171-205',
            '31' => '176-210',     
        ];
        
        foreach ($size_types as $size_type) {
            foreach ($foot_lengths as $foot_length => $dataHeight) {
                $foot_length = (float) $foot_length;
                echo $foot_length . "\n";
                list($hMin, $hMax) = explode('-', $dataHeight);
                
                for ($height=$hMin; $height <=$hMax; $height++) {
                    for ($age=20; $age <=60; $age++) {
                        for ($weight=50; $weight <=115; $weight++) {
                            $data = compact('size_type', 'height', 'age', 'weight', 'foot_length');
                            $model = ParserModel::create(['request' => json_encode($data)]);
                            echo $model->id . "\n";
                        }                
                    }
                }
            }
        }
    }
    
}
