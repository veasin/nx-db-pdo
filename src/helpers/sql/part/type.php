<?php
declare(strict_types=1);
namespace nx\helpers\db\sql\part;

enum type: string {
	case VALUE = 'value';
	case FIELD = 'field';
	case FUNCTION = 'function';
}