<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'position', 'salary', 'hire_date', 'department_id', 'role_id'];

    public function department(){
        return $this-> belongsTo(Department::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }

}
