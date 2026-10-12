<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'FULL_TIME';
    case PartTime = 'PART_TIME';
    case Contract = 'CONTRACT';
    case Internship = 'INTERNSHIP';
}
