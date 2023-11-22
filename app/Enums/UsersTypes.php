<?php namespace App\Enums;

enum UsersTypes: string
{
	case SUPPER = "system_admin";
	case ADMIN = "admin";
	case USER = "user";
}
