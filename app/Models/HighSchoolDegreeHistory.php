<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HighSchoolDegreeHistory extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'high_school_degrees_history';
    protected $primaryKey = 'IDENT';


    /**
     * Indicates if the model should be timestamped.
     * The table uses UPDATE_ON column managed by the DB.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'SEC_SCHOOL_YEAR',
        'SEC_SCHOOL_SEATNO',
        'STUDENT_NAME',
        'SEC_SCHOOL_MARK',
        'FINAL_STATUS',
        'SEC_SCHOOL_RATE',
        'SEC_SCHOOL_PROVINCE',
        'SEC_SCHOOL_TERRITORY',
        'SEC_SCHOOL_TYPE',
        'SEC_SCHOOL_NAME',
        'SEC_SCHOOL_PLACE',
        'PLACE_OF_BIRTH',
        'DATE_OF_BIRTH',
        'PROVINCE',
        'TERRITORY',
        'NATIONALITY',
        'COUNTRY_NAME',
        'COUNTRY_IDENT',
        'YEMEN_NATIONAL',
        'GENDER',
        'NATIONALITY_NAME',
        'UPDATE_BY',
        'NOTES',
    ];
}
