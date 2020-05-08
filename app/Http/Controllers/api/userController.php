<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hash;
// include models
use App\User;
// include api resources
use App\Http\Resources\api\userCollection;
use Image;

// use Carbon\Carbon;

class userController extends Controller
{
     /**
      * Create a controller instance
      *
      * @return void
      */
    public function __construct(){
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return new userCollection(User::latest()->paginate(2));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:5|max:255',
            'type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:255',
        ]);

        User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'type' => $request['type'],
            'bio' => $request['bio'],
            'photo' => $request['photo']
        ]);

        return response()->json([
            'message' => 'data is successfully added'
        ]);

        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:5|max:255',
            'type' => 'required|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:255',
        ]);

        $user->update($request->all());

        return response()->json([
            'message' => 'data is successfully updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $this->authorize('isAdmin');
        User::findOrFail($id)->delete();

        return response()->json([
            'message' => 'User is successfully deleted'
        ]);
        
    }

    /**
     * Get user profile
     * 
     * @return userProfile
     */
    public function profile(){
        return auth('api')->user();
    }

    /**
     * update user profile
     * 
     * @return success or fail message
     */
    public function updateProfile(Request $request){
        $user = auth('api')->user();
        // return $request->all();
        $validatedData = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:5|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|string',
        ]);
        $userOldPic = $user->photo;
        if($request->photo != $userOldPic){
            $name = time().'.'. explode(';',  explode('/',  $request->photo)[1])[0];
            Image::make($request->photo)->save(public_path('img/profile/').$name);
            
            $request->merge(['photo' => $name]);
            $userOldPicLoc = public_path('img/profile/').$userOldPic;
            if(file_exists($userOldPicLoc)){
                @unlink($userOldPicLoc);
            }
        }
        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->all());

        return ['message'=> 'Profile is successfully updated', 'data' => $user ];
    }

    /** 
     * find user
     * 
     * @return userDatails
     */
    
    public function findUser(Request $request){
        if($request->q){
            $user = User::where('name','like',"%$request->q%")->orWhere('email','like',"%$request->q%")->paginate(2);
        } else {
            $user = User::latest()->paginate(2);
        }
        return $user;
    }

}
