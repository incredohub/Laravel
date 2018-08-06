<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Contracts\Admin\ProfilesServiceInterface;
use Illuminate\Contracts\Auth\Guard;
use App\Helpers\Countries;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class ProfilesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('user:wholesaler');
    }

    public function index( ProfilesServiceInterface $profileService, Guard $auth, Countries $countries )
    {
        $admin = $profileService->index( $auth->user()->getAuthIdentifier() );

        return view('user.profile',
            [
                'admin' => $admin,
                'countries' => $countries->countries,
            ]);
    }

    public function update( ProfilesServiceInterface $profileService, Request $request, Guard $auth, $id )
    {
        $this->validate($request, [
            'first_name' => 'string|min:3|max:16',
            'last_name' => 'string|min:3|max:16',
            'phone' => 'regex:/^([0-9]{3})\)?[-. ]?([0-9]{2})?[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})$/',
            'alternative_phone' => 'regex:/^([0-9]{3})\)?[-. ]?([0-9]{2})?[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})[-. ]?([0-9]{2})$/',
            'address' => 'string|min:3',
            'brand_image' => 'image|mimes:jpeg,jpg,png|dimensions:min_width=250,min_height=250',
        ]);

        $data = $request->all();

        $image = $request->file('profile_image');

        if( null !== $image ){
            $destinationPath = public_path('uploads/profiles');

            $ext = $image->getClientOriginalExtension();

            $image_name = time().'.'.$ext;

            $pathImg = $destinationPath.'/'.$image_name;

            $image->move($destinationPath, $image_name);
            //move_uploaded_file($image, $pathImg);

            $img = $this->resize_image($ext, $pathImg, 200, 200);

            if($ext == "jpg" || $ext == "jpeg") {
                imagejpeg($img, $pathImg);
            } elseif($ext == "png") {
                imagepng($img, $pathImg);
            } elseif($ext == "gif") {
                imagegif($img, $pathImg);
            }

            $data['profile_image'] = $image_name;
        }

        $data['id'] = $auth->user()->getAuthIdentifier();

        $survey = $profileService->update($data);

        if($survey){
            return  \Redirect::back();
        }
    }

    private function resize_image( $ext, $file, $w, $h, $crop=FALSE ) {

        list($width, $height) = getimagesize($file);
        $r = $width / $height;

        if(round($r) == 1) {
            $w = 200;
            $h = 200;
        } elseif(round($r) > 1) {
            $w = 250;
            $h = 200;
        } else {
            $w = 200;
            $h = 250;
        }

        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $width;
            $newheight = $height;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
            //$newwidth = $w;
            //$newheight = $h;
        }

        if($ext == "jpg" || $ext == "jpeg") {
            $src = imagecreatefromjpeg($file);
        } elseif($ext == "png") {
            $src = imagecreatefrompng($file);
        } elseif($ext == "gif") {
            $src = imagecreatefromgif($file);
        }else {
            $src = imagecreatefromjpeg($file);
        }

        $result = imagecreatetruecolor($newwidth, $newheight);
        imagesavealpha($result, true);

        $trans_colour = imagecolorallocatealpha($result, 0, 0, 0, 127);
        imagefill($result, 0, 0, $trans_colour);

        $red = imagecolorallocate($result, 255, 0, 0);
        imagefilledellipse($result, 400, 300, 400, 300, $red);

        imagecopyresampled($result, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $result;
    }

}
