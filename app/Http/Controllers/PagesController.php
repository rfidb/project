<?php

namespace App\Http\Controllers;

use App\Carcass;
use App\Genotyping;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Input;
use Schema;
use Session;
use App\User;
use Mail;
use Redirect;

class PagesController extends Controller
{


    public function genotyping()
    {
        $query = DB::table('genotyping');
        $results = $query->get();
        return view('pages.queryResults', compact('results'));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function carcass()
    {
        $query = DB::table('carcass');
        $results = $query->get();
        return view('pages.queryResults', compact('results'));
    }

    public function carcassForm()
    {
        return view('pages.carcassForm');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function carcassQuery()
    {

        $idpig_op = $_POST["idpig_op"];
        $idpig = $_POST["idpig"];
        $slaughter_date_op = $_POST["slaughter_date_op"];
        $slaughter_date = $_POST["slaughter_date"];
        $weight_op = $_POST["weight_op"];
        $weight = $_POST["weight"];
        $length_op = $_POST["length_op"];
        $length = $_POST["length"];
        $bf_10_op = $_POST["bf_10_op"];
        $bf_10 = $_POST["bf_10"];
        $bf_last_rib_op = $_POST["bf_last_rib_op"];
        $bf_last_rib = $_POST["bf_last_rib"];
        $bf_last_lum_op = $_POST["bf_last_lum_op"];
        $bf_last_lum = $_POST["bf_last_lum"];


        $query = DB::table('carcass');

        //        if (Input::has('idpig'))
        //            dd('sucess');

        if ($idpig != null && $idpig_op != null)
            $query->where('idpig', $idpig_op, $idpig);

        if ($slaughter_date_op != null && $slaughter_date != null)
            $query->where('slaughter_date', $slaughter_date_op, $slaughter_date);

        if ($weight_op != null && $weight != null)
            $query->where('weight', $weight_op, $weight);

        if ($length_op != null && $length != null)
            $query->where('length', $length_op, $length);

        if ($bf_10_op != null && $bf_10 != null)
            $query->where('bf_10', $bf_10_op, $bf_10);

        if ($bf_last_rib != null && $bf_last_rib_op != null)
            $query->where('bf_last_rib', $bf_last_rib_op, $bf_last_rib);

        if ($bf_last_lum != null && $bf_last_lum_op != null)
            $query->where('bf_last_lum', $bf_last_lum_op, $bf_last_lum);


        $results = $query->get();
        return view('pages.queryResults', compact('results'));
    }


    public function index()
    {
        return view('welcome');
    }

    public function notdone()
    {
        return view('errors.notdone');
    }

    public function advancedSearchForm()
    {
        return view('pages.sqlSearch');
    }

    public function sqlQuery(Request $resuqest)
    {
//        dd($resuqest->except("_token")["sqlScript"]);
        $script = $resuqest->except("_token")["sqlScript"];

        try{
            $results = DB::select($script);
        }catch(\Exception $e) {
            return "Your request failed, please check your search parameters.";
        }
        return view('pages.queryResults', compact('results'));
    }

    public function advanceQueryForm()
    {
        return view('pages.advanceQueryForm');
    }

    public function queryResults(Request $resuest)
    {
        //gell all input name and value except token
        $inputs = $resuest->except("_token");
        $table_list = ($inputs["table_list"]);
        //put table names in an array
        $tables = preg_split('/[\ \,]+/', $table_list);

        $query = DB::table($tables[0]);
        //inner join tables
        for ($i = 1; $i < count($tables); ++$i) {
            $query->join($tables[$i], $tables[0] . ".idpig", "=", $tables[$i] . ".idpig");
        }
        //Select attribute list
        if ($inputs["attribute_list"] != null) {
            $query->selectRaw($inputs["attribute_list"]);
        }
        //add where clause
        for ($i = 0; $i < count($inputs["expr"]); ++$i) {
            $logic = strtolower(preg_replace('/\s+/', '', $inputs["logic"][$i]));
            //dd($logic);
            if ($logic === "and") {
                $query->whereRaw($inputs["expr"][$i]);
            } elseif ($logic === "or") {
                $query->orWhereRaw($inputs["expr"][$i]);
            }
        }
        $results = $query->get();
        return view('pages.queryResults', compact('results'));
    }

//    public function postRegister() {
//        return view('auth.postRegister');
//    }


    public function confirm($activation_code)
    {

//        dd($activation_code);
        if (!$activation_code) {
            throw new InvalidConfirmationCodeException;
        }

        $user = User::where('activation_code', $activation_code)->first();
//        dd($user);

        if (!$user) {
            throw new InvalidConfirmationCodeException;
        }

        $user->activation_code = "";
        //$user->status = 1;
        $user->save();


        $data = array(
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'organization' => $user->organization,
            'reason' => $user->reason,
        );


//        //send email to user
//        Mail::send('email.activated', $data, function ($message) use (&$user) {
//            $message->to($user->email, $user->first_name . " " . $user->last_name)
//                ->subject('Residual Feed Intake Registration email address confirmed');
//        });

        //send email to inform administrator
        Mail::send('email.request', $data, function ($message) use (&$user) {
            $message->to(env('MAIL_USERNAME'), env('ADMIN_NAME'))
                ->subject('Residual Feed Intake Database account registration request');
        });


        return view('auth/confirm');
    }

    public function approval($id)
    {

        $user = User::where('id', $id)->first();

//        dd($user);

        if ( ! $user)
        {
            throw new InvalidConfirmationCodeException;
        }

        $user->status = 1;
        $user->save();


        $data = array(
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'organization' => $user->organization,
            'reason' => $user->reason,
        );


        //send email to user
        Mail::send('email.activated', $data, function($message) use (&$user) {
            $message->to($user->email, $user->first_name." ".$user->last_name)
                ->subject('RFIDB: Account application approved');
        });


        return Redirect::action('Auth\AdminController@dash');
    }




    public function profile($id)
    {
        $user = User::where('id', $id)->first();

        return view('auth/profile', compact('user'));
    }

    public function update($id, Request $request)
    {
//        dd($request->input('role'));
        $user = User::find($id);
        $user->first_name = $request->input('first_name');
        $user->email = $request->input('email');
        $user->last_name = $request->input('last_name');
//        $user->role = $request->input('role');
        $user->organization = $request->input('organization');
        $user->reason = $request->input('reason');
//        $user->password = bcrypt($request->input('password'));
        $user->save();
//        dd($request->input('role'));

        return Redirect::back()->withInput();
    }

    public function getResetPass($id)
    {
//        dd("controller for user setting password");
        return view('auth/reset', compact('id'));
    }


    public function showDatabase()
    {
        $data = array(
            "lps",
            "others",
        );
        return view('pages.database', compact('data'));
    }

    public function showTable(Request $resuest)
    {
//        $inputs = $resuest->except("_token");
//        dd($inputs["database"]);
        $tablelist = DB::select('SHOW TABLES');
//        dd($tables);
        $tables = array();
        foreach ($tablelist as $table) {
            array_push($tables, $table->Tables_in_rfidb);
        }
//        dd($tables);
        /*if ($inputs["database"] === "lps") {
            $tables = array("lps_rfi",
                "lps_inventory",
                "lps_rnaseq",);
        } else {
            $tables = array_diff($tables, array(
                "lps_rfi",
                "lps_inventory",
                "lps_rnaseq",
                "users",
                "password_resets",
                "migrations",
            ));
        }*/
        $tables = array_diff($tables, array(
            "users",
            "password_resets",
            "migrations",
        ));
//        dd($tables);
        return view('pages.browser', compact('tables'));
    }

    public function getAttr(Request $resuest)
    {
        $inputs = $resuest->except("_token");
        if (count($inputs) === 0) return 'Please select at least one table';

        $data = array();
        $tables = $inputs["table"];

        for ($i = 0; $i < count($tables); ++$i) {
            $columns = Schema::getColumnListing($tables[$i]);
            $data[$tables[$i]] = $columns;
        }

        return view('pages.attr', compact('data'));
    }

    public function getResult(Request $request)
    {
        $allinputs = $request->except("_token");
//        dd($allinputs);
        if (!array_key_exists("attr", $allinputs)) return 'Please select at least one attribute';

        $filter = false;
        if (array_key_exists("filter", $allinputs)) $filter = true;
//        dd($filter);
//        dd(count($inputs));
        $inputs = $allinputs["attr"];
//        if (count($inputs) === 0) return 'Please select at least one attribute';
//        dd($inputs);

        //get tables list in array $tables
        $tables = array();
        //only add table when this variable changes, so tables won't have duplicate values
        $tableTemp = "";
//        $flag = false;
        foreach ($inputs as $input) {
//            dd($input);
            $names = explode(".", $input);
 /*           if ($names[0] === "lps_rfi"
                || $names[0] === "lps_inventory"
                || $names[0] === "lps_rnaseq"
            )
                $flag = true;*/
            if ($names[0] != $tableTemp) {
                $tableTemp = $names[0];
                array_push($tables, $names[0]);
            }
        }
//        dd($tables);
        $joinAttr1 = ".idpig";
        $joinAttr2 = "";

//        if ($flag)
//            $joinAttr = ".eartag";

//        dd ($joinAttr);
        if($tables[0] == "dam_mate")
            $joinAttr1 = ".iddam";
        $query = DB::table($tables[0]);
        //inner join tables
        $lps_rfiAdded = false;
        for ($i = 1; $i < count($tables); ++$i) {
            //join table[i] accordingly
            if($tables[$i] == "dam_mate") {
                $joinAttr2 = ".iddam";
            } else {
                $joinAttr2 = ".idpig";
            }

            $query->join($tables[$i], $tables[0] . $joinAttr1, "=", $tables[$i] . $joinAttr2);
        }
        //Select attribute list
        $attribute_list = "";
        foreach ($inputs as $input) {
//            dd($input);
            $attribute_list = $attribute_list . $input . ",";
        }
        //trim: get rod of ","
        $attribute_list = rtrim($attribute_list, ",");
//        dd($attribute_list);
//        dd($query->selectRaw('count(*)')->get());

        $query->selectRaw($attribute_list);
//        dd($query->toSql());
        //in case we need filters
        if ($filter) {
            $expressions = $allinputs["expr"];
            $logics = $allinputs["logic"];
            //add where clause
            for ($i = 0; $i < count($expressions); ++$i) {
                $logic = strtolower(preg_replace('/\s+/', '', $logics[$i]));
                //dd($logic);
                if ($logic === "and") {
                    $query->whereRaw($expressions[$i]);
                } elseif ($logic === "or") {
                    $query->orWhereRaw($expressions[$i]);
                } else {
                    return "filter logic can only be AND or OR";
                }
                if(!$expressions[$i]) return "invalid expression input!";
            }
        }
        try{
            $results = $query->get();
        }
        catch(\Exception $e) {
            return "Your request failed, please check your search parameters.";
        }
//        $results = $query->get();
//        dd($results);
//        dd($query->toSql());
        return view('pages.queryResults', compact('results'));
    }

}
