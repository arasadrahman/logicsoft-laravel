<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = "users";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        "ClientID",
        "ShopName",
        "ShopPrefix",
        "Logo",
        "UserName",
        "Password",
        "Mobile",
        "Email",
        "Status",
        "EntryDT",
        "LastLogin",
    ];

    protected $hidden = ["Password", "remember_token"];

    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    public function getAuthIdentifierName(): string
    {
        return "id";
    }
}
