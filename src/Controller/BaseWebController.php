<?php

namespace AMBERSIVE\Api\Controller;

use Illuminate\Routing\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response as Rep;

use App;
use File;
use Str;
use Parsedown;

class BaseWebController extends Controller
{
    public function __construct(){

        $gzip     = config('ambersive-api.gzip', false);

        // Deactivate the response gzip for this response
        if (App::environment() === 'development' || App::environment() === 'testing') {
            $gzip = false;
        }

        // Handle gzip for request
        if ($gzip === true) {
            if    (!in_array('ob_gzhandler', ob_list_handlers())) {
                ob_start('ob_gzhandler');
            }
            else  {
                ob_start();
            }
        }

    }
    
    /**
     * Respond a file as download from a secure place.
     * Caution: Do make this controller methods accessable via routes!!!
     *
     * @param  mixed $request
     * @param  mixed $path
     * @return void
     */
    public function respondFileAsDownload(Request $request, string $path = null, string $name = null, bool $deleteAfterDownload = false) {

        if (File::exists($path) === false) {
            abort(404);
        }

        $mimeType = File::mimeType($path);     
        $name     = $name != null ? $name : File::name($path).'.'.File::extension($path);   

        $headers = array(
            "Content-Type: ${mimeType}",
        );

        if ($deleteAfterDownload == true) {
            return response()->download($path, $name, $headers)->deleteFileAfterSend();
        }

        return response()->download($path, $name, $headers);

    }
    
    /**
     * Respond a file directly in the browser
     *
     * @param  mixed $request
     * @param  mixed $path
     * @param  mixed $customHeaders
     * @return void
     */
    public function respondFileInBrowser(Request $request, string $path = null, array $customHeaders = []) {

        if (File::exists($path) === false) {
            abort(404);
        }

        $mimeType = File::mimeType($path);     

        $headers = array_merge(array(
            "Content-Type: ${mimeType}",
        ), $customHeaders);

        // If the given file is a markdown file it will return as a html file
        if (Str::endsWith($path, '.md') === true) {

            $parsedown = Parsedown::instance();
            $parsedown->setSafeMode(false);

            return Rep::make($parsedown->text(File::get($path)), 200, [
                "Content-Type: text/html"
            ]);

        }

        return response()->file($path, $headers);

    }

}
