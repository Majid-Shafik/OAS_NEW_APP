<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboValue extends Model
{
    protected $table = 'combe_values';
    
    // assuming it might not have created_at/updated_at
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $fillable = [
        'CODE',
        'VALUE',
    ];

    /**
     * Get a list of options (ID => VALUE) for a specific combo CODE.
     *
     * @param int $code
     * @return array
     */
    public static function getOptionsByCode(int $code): array
    {
        return self::where('CODE', $code)->pluck('VALUE', 'ID')->toArray();
    }

    /**
     * Get a list of options (VALUE => VALUE) for a specific combo CODE.
     *
     * @param int $code
     * @return array
     */
    public static function getOptionsValuesByCode(int $code): array
    {
        return self::where('CODE', $code)->pluck('VALUE', 'VALUE')->toArray();
    }

    /**
     * Get the label (VALUE) for a specific ID and CODE.
     *
     * @param int $code
     * @param mixed $id
     * @return string
     */
    public static function getLabel(int $code, $id): string
    {
        if (empty($id)) {
            return '-';
        }

        if ($id instanceof \BackedEnum) {
            $id = $id->value;
        } elseif ($id instanceof \UnitEnum) {
            $id = $id->name;
        }

        $value = self::where('CODE', $code)->where('ID', $id)->value('VALUE');
        return $value ?? (string)$id;
    }

    /**
     * Relationship to Applicants by SEC_SCHOOL_TYPE (Code 1)
     */
    public function secSchoolApplicants()
    {
        return $this->hasMany(Applicant::class, 'SEC_SCHOOL_TYPE', 'VALUE');
    }

    /**
     * Relationship to Applicants by GENDER (Code 6)
     */
    public function genderApplicants()
    {
        return $this->hasMany(Applicant::class, 'GENDER', 'VALUE');
    }

    /**
     * Relationship to Applicants by IDENT_TYPE (Code 7)
     */
    public function identTypeApplicants()
    {
        return $this->hasMany(Applicant::class, 'IDENT_TYPE', 'VALUE');
    }

    /**
     * Relationship to Applicants by BLOOD_GROUP (Code 8)
     */
    public function bloodGroupApplicants()
    {
        return $this->hasMany(Applicant::class, 'BLOOD_GROUP', 'VALUE');
    }
}
