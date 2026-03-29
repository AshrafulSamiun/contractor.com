@echo off
setlocal
set FLUTTER_SDK=C:\src\flutter
set JAVA_HOME=C:\Java\jdk-17.0.10+7
set ANDROID_HOME=C:\Android
set DART_BIN=%FLUTTER_SDK%\bin\cache\dart-sdk\bin\dart.exe
set FLUTTER_TOOL=%FLUTTER_SDK%\bin\cache\flutter_tools.snapshot

set PATH=%JAVA_HOME%\bin;%ANDROID_HOME%\platform-tools;%ANDROID_HOME%\cmdline-tools\latest\bin;%PATH%

if not exist "%DART_BIN%" (
  echo Dart not found at %DART_BIN%
  exit /b 1
)

"%DART_BIN%" "%FLUTTER_TOOL%" %*
endlocal
