<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Yajra\DataTables\DataTables;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile_no',
        'password',
        'role',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public static function getTableData($request) {

        $query  = self::query();
        $query->where('role', 'user');

        $query->orderBy('created_at', 'desc');


      
        $table = DataTables::of($query)
      
                ->editColumn('created_at', function ($request) {
                    if ($request->created_at) {
                        return convertUtcToTimezone($request->created_at);
                    }
                    return '--';
                })->editColumn('name', function ($request) {
                    return $request->name;
                })
                
                ->editColumn('email', function ($request) {
                    return $request->email;
                })
            
                ->editColumn('mobile_no', function ($request) {
                    return $request->mobile_no;
                })
                ->editColumn('gender', function ($request) {
                    return $request->city;
                })
                ->addColumn('action', function ($q) {
                    $params['is_delete'] = 1;
                  
                    $params['model'] = $q;
             
                    $params['delete_route'] = route('admin.users');

                    $params['name'] = '-';
                    return view('admin.datatable.action', $params)->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        return $table->original;
    }
}
