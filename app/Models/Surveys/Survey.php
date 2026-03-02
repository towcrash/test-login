<?php

namespace App\Models\Surveys;

use App\Traits\SurveyTables;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
	use SurveyTables;

	protected $connection = 'survey';

	public $timestamps = false;
}
