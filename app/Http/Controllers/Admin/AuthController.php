<?php

namespace TemplateInicial\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TemplateInicial\Contract;
use TemplateInicial\Http\Controllers\Controller;
use TemplateInicial\Property;
use TemplateInicial\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // $user = User::where('id', 1)->first();
        // $user->password = md5('teste');
        // $user->save();

        if(Auth::check() === true) {
            return redirect()->route('admin.home');
        }
        return view('admin.index');
    }

    public function home()
    {
        $lessors = User::lessors()->count();
        $lessees = User::lesseess()->count();
        $team = User::where('admin', 1)->count();

        $propertiesAvailable = Property::available()->count();
        $propertiesUnavailable = Property::unavailable()->count();
        $propertiesTotal = $propertiesAvailable + $propertiesUnavailable;

        $contractsPendent = Contract::pendent()->count();
        $contractsCanceled = Contract::canceled()->count();
        $contractsActive = Contract::active()->count();
        $contractsTotal = $contractsPendent + $contractsCanceled + $contractsActive;

        $contracts = Contract::orderBy('id', 'DESC')->limit(10)->get();

        $properties = Property::orderBy('id', 'DESC')->limit(3)->get();

        return view('admin.dashboard', [
            'lessors' => $lessors, 
            'lessees' => $lessees,
            'team' => $team,
            'propertiesAvailable' => $propertiesAvailable,
            'propertiesUnavailable' => $propertiesUnavailable,
            'propertiesTotal' => $propertiesTotal,
            'contractsPendent' => $contractsPendent,
            'contractsCanceled' => $contractsCanceled,
            'contractsActive' => $contractsActive,
            'contractsTotal' => $contractsTotal,
            'contracts' => $contracts,
            'properties' => $properties
        ]);
    }

    public function login(Request $request)
    {
        
        if(in_array('', $request->only('email', 'password'))) {
            $json['message'] = $this->message->error('Oops, informe todos os dados para efetuar o login')->render();
            return response()->json($json);
        }

        if(!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            $json['message'] = $this->message->error('Oops, informe um email valido')->render();
            return response()->json($json);        
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        
        if(!Auth::attempt($credentials)){
            $json['message'] = $this->message->error('Oops, usuário e senha não conferem')->render();
            return response()->json($json);
        }


        $this->authenticated($request->getClientIp());
        $json['redirect'] = route('admin.home');
        return response()->json($json);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }

    private function authenticated(string $ip)
    {
        $user = User::where('id', Auth::user()->id);
        $user->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ]);
    }
}
