<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/*Kit de autenticacion de usuarios
este modulo nos permite implementar de manera muy rapida la capa
de auenticacion de usuarios en nuestra aplicación web */
class AuthController extends Controller
{
    /*constructor: Establecemos esta función en nuestra clase
    controladora para poder utilizar el middleware auth:api
    dentro de ella para bloquear el acceso no autenticado
    a ciertos métodos dentro del controlador */
    public function __construct()
    {
            $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /* login: Este método autentica a un usuario con su correo
    electrónico y contraseña. Cuando un usuario se autentica
    correctamente, el método attempt() de la fachada Auth
    devuelve el token JWT. El token generado
    se recupera y se devuelve como JSON con el objeto de usuario*/
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'require|string|email',
            'password' => 'require|string',
        ]);

        $credentials = $request->only('email', 'password');


        $token = Auth::attempt($credentials);
        if (!$token){
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' =>$user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ],
        ]);
    }
    /*registrar: Este método crea el registro de usuario e
    inicia la sesión del usuario con las generaciones de tokens */
    public function register(Request $request){
        $request->validate([
            'name' =>'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),

        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'aurhorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /*cerrar sesión: Este método invalida el token Auth del usuario */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Succesfully logged out',
        ]);
    }

    /*refresh: Este método invalida el token Auth
    del usuario y genera un nuevo token */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer'
            ]
            ]);
    }
}
