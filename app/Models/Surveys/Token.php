<?php

namespace App\Models\Surveys;

use App\Traits\SurveyTables;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
	use SurveyTables;

	protected $connection = 'survey';

	protected $fillable = [
		'email',
		'emailstatus',
		'token',
		'language',
		'sent',
		'remindersent',
		'remindercount',
		'usesleft',
		'attribute_40',
	];

	public $timestamps = false;
}
