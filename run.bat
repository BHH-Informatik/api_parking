@echo off

setlocal

set "arguments=%*"

@REM if no arguments are passed, then run the default command
if "%arguments%"=="" (
	goto run
)

switch "%arguments%" (
	case "run" goto run
	case "build" goto build
	case "clean" goto clean
	case "test" goto test
	case "help" goto help
	default goto help
)

:run
	gradlew.bat run

:build
	gradlew.bat build


:help
	echo "Usage: run.bat [run|build|clean|test|help]"