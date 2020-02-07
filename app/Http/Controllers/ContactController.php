<?php

namespace App\Http\Controllers;
use App\Sections;
use App\Contacts;
use App\Translations;
use Illuminate\Http\Request;
use Google\Cloud\Translate\TranslateClient;
class ContactController extends Controller
{
    //
    public function index(){
        $json = file_get_contents(storage_path('sample.json'));
        $objs = json_decode($json,true);
        foreach ($objs as $obj){
            $section= new Sections;
            $section->save();
            $names=$this->convert_stringArray_to_array($obj['names']);
            $hits=$this->convert_stringArray_to_array($obj['hits']);
            for ($x = 0; $x <= count($hits)-1; $x++) {
                $contact=new Contacts;
                $contact->name=$names[$x];
                $contact->hits=$hits[$x];
                $section->contacts()->save($contact);
            }
        }
        return $objs;
    }

    public function translate(){
        $apiKey="";//add the api key here
        $contacts=Contacts::all();
        set_time_limit(0);
        foreach ($contacts as $contact ){
            $name=$contact->name;
            if(!(Translations::where('name', $name)->exists())){
                $translation= new Translations;
                $translation->name=$name;
                $translate = new TranslateClient([
                    'key' => $apiKey
                ]);
                $contains_arabic = preg_match('/\p{Arabic}/u', $name);//check if contains at least one arabic letter
                $contains_english = preg_match('/.*[A-Za-z].*/', $name);//check if contains at least one english letter
                if($contains_arabic){
                    $result = $translate->translate($name, [
                        'source'=> 'ar',
                        'target' => 'en'
                    ]);
                    $translation->enTranslation= $result['text'];
                }
                else{
                    $translation->enTranslation= $name;
                }
                if($contains_english){
                    $result = $translate->translate($name, [
                        'source'=> 'en',
                        'target' => 'ar'
                    ]);
                    $translation->arTranslation= $result['text'];
                }
                else{
                    $translation->arTranslation= $name;
                }
                if(!($contains_arabic or $contains_english)){
                    $translation->enTranslation= $name;
                    $translation->arTranslation= $name;
                }
                $translation->save();
            }
            $contact->hits=($contact->hits)+1;
            $contact->save();
        }
        return "Done!";
    }


    public function convert_stringArray_to_array($stringArray){
        $stringArray=str_replace('\\','',$stringArray);
        $stringArray=str_replace('"','',$stringArray);
        $stringArray=str_replace('[','',$stringArray);
        $stringArray=str_replace(']','',$stringArray);
        $array=explode(',',$stringArray);
        return $array;
    }
}
