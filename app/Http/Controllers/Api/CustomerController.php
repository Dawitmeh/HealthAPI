<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $customers = User::where('role', 'user')->with('subscription', 'payment')->get();

            $customers->transform(function ($customer) {
                $customer->avatar = asset('storage/' . $customer->avatar);
                return $customer;
            });

            return response()->json([
                'data' => $customers
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|max:15|unique:users',
                'password' => 'required|string|confirmed',
                'avatar' => 'nullable|string'
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            $rawPhone = ltrim($request->phone, '0');
            $phone = '+251' . $rawPhone;

            if (isset($data['avatar'])) {
                $relativePath = $this->saveImage($data['avatar']);
                $data['avatar'] = $relativePath;
            }

            $customer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'avatar' => $data['avatar'],
                'password' => Hash::make($request->password),
                'role' => 'user'
            ]);

            return response()->json([
                'data' => $customer
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $customer = User::with('subscription', 'payment')->where('id', $id)->first();

            if (!$customer) {
                return response()->json([
                    'error' => 'Customer not found'
                ], 404);
            }

            $customer->avatar = asset('storage/' . $customer->avatar);

            return response()->json([
                'data' => $customer
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    { 
        try {
            $customer = User::findOrFail($id);

            $validateData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    Rule::unique('users')->ignore($id),
                ],
                'phone' => [
                    'required',
                    'string',
                    Rule::unique('users')->ignore($id),
                ],
                'password' => 'nullable|string|confirmed',
                'avatar' => 'nullable|string'
            ], [

                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone has already been taken',
                'password.confirmed' => 'The password confirmation does not match'
                
            ]);

            $rawPhone = ltrim($request->phone, '0');
            $phone = '+251' . $rawPhone;

            if (isset($validateData['avatar']) && Str::startsWith($validateData['avatar'], 'data:image')) {
                $relativePath = $this->saveImage($validateData['avatar']);
                $validateData['avatar'] = $relativePath;

                if ($customer->avatar) {
                    $absolutePath = public_path('storage/' . $customer->avatar);
                    File::delete($absolutePath); 
                }
            } else {
                unset($validateData['avatar']);
            }

            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'avatar' => $validateData['avatar'] ?? $customer->avatar,
                'role' => 'user',
                'password' => $request->filled('password') ? Hash::make($request->password) : $customer->password
            ]);

            return response()->json([
                'data' => $customer
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $customer = User::findOrFail($id);
            $customer->delete();

            return response()->json([
                'message' => 'Customer deleted successfully!'
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    private function saveImage($image) 
    {
        // check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {

            $image = substr($image, strpos($image, ',') + 1);

            // get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception(('invalid image type'));
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }
        
        // correct path: storage/app/public/hospitals
        $fileName = Str::random() . '.' . $type;
        $relativePath = 'customers/' . $fileName;
        $storagePath = storage_path('app/public/' . $relativePath);

        // make sure the directory exists
        if (!File::exists(dirname($storagePath))) {
            File::makeDirectory(dirname($storagePath), 0755, true);
        }

        file_put_contents($storagePath, $image);

        // return 'storage/' . $relativePath;
        return $relativePath;
    }
}
