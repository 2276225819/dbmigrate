<?php
$path=@array_unshift($argv);//
$cmd =@array_unshift($argv);
switch ($cmd) {
	case 'pull': return $sync->pull(in_array('-f',$argv)); 
	case 'push': return $sync->push(in_array('-f',$argv));
	default:
		$debug = debug_backtrace();
		$name = basename(end($debug)['file']);
		echo "Usage: php {$name} [cmd] [-f]"  
		."\n  pull       从数据库拉取表结构到本地表结构"
		."\n        -f   (危险)覆盖本地表结构"
		."\n  push       将本地表结构推到数据库"
		."\n        -f   (危险)删除数据库多余表和字段"
		."\n";
} 