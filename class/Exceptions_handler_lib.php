<?php
/**
 * Biblioteka klas obsługi wyjatków.
 * 
 */

class MySQLIConnectException extends Exception 
{
	function __construct($inErrmsg, $inErrcode)
	{
		header ('Content-type: text/html; charset=utf-8');
		echo ("Wystąpił błąd podczas próby połączenia z bazą danych. Dalsza praca może okazać się niemożliwa. Spróbuj ponownie póżniej lub powiadom administratora systemu");
		die;
	}	
}

class NoDataBaseHandlerException extends Exception 
{
	function __construct()
	{
		parent::__construct("BRAK POŁĄCZENIA Z BAZĄ DANYCH, DALSZA PRACA NIEMOŻLIWA!!!");
	}	
}


class DataToLongException extends Exception 
{
	function __construct($in_errmsg)
	{
		parent::__construct("Wprowadzono nieprawidłowe dane. Najprawdopodobniej wprowadzone dane są zbyt długie. Limity wynoszą: nazwa użytkownika: max 50 znaków, adres e-mail: max 50 znaków, wiadomość: max 1000 znaków.  ($in_errmsg)");
	}	
}

class InvalidUsernameException extends  Exception 
{
	function __construct()
	{
		parent::__construct("Nieprawidłowa nazwa użytkownika!");
		
	}
}


class InvalidEmailException extends  Exception 
{
	function __construct()
	{
		parent::__construct("Nieprawidłowy adres e-mail!");
		
	}
}


class InvalidMessageException extends  Exception 
{
	function __construct()
	{
		parent::__construct("Wiadomość nie może być pusta!");
		
	}
}

class AddNewGuestbookEntryException extends Exception
{
	function __construct ($in_errormsg, $in_errcode)
	{
		parent::__construct("Przepraszamy, nie można dodać wpisu.
		Treść komunikatu o błędzie: {$in_errormsg}",$in_errcode);
	}
}

class PreparedInstructionException extends  Exception 
{
	function __construct($in_errormsg,$in_errorcode)
	{
		parent::__construct("Instrukcja preparowana zakończona niepowodzeniem:
		{$in_errormsg}",$in_errorcode);
	}
}

class BindParamOrExecuteException extends  Exception 
{
	function __construct($in_errormsg,$in_errorcode)
	{
		parent::__construct("Instrukcja bind lub execute zakończona niepowodzeniem:
		{$in_errormsg}",$in_errorcode);
	}
}

class ReadFromDatabaseException extends  Exception 
{
	function __construct($in_errormsg,$in_errorcode)
	{
		parent::__construct("Odczyt z bazy danych zakończony niepowodzeniem:
		{$in_errormsg}",$in_errorcode);
	}
}
	
class InvalidVariableNameException extends Exception 
{
	public function __construct($in_errormsg)
	{
		parent::__construct("Niepoprawna nazwa zmiennej ".$in_errormsg);
	}	
}

class IncorrectValueException extends Exception 
{
	public function __construct($in_errormsg)
	{
		parent::__construct("Niepoprawna wartość zmiennej ".$in_errormsg);
	}	
}

class  MysqliQueryException extends Exception 
{
	public function __construct($in_errormsg)
	{
		parent::__construct("Błąd zapytania mysqli - treść komunikatu o błędzie: ".$in_errormsg);
	}	
}