@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1


del OpenCart1-Acumulus-%version%.zip 2> nul

rem zip package.
"C:\Program Files\7-Zip\7z.exe" a -tzip OpenCart1-Acumulus-%version%.zip acumulus | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t OpenCart1-Acumulus-%version%.zip | findstr /i "Processing Everything Failed Error"
