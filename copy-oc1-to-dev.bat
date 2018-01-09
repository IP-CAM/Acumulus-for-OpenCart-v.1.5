@echo off
rem Ensure that all sub directories exist in development installation.
mkdir D:\Projecten\Acumulus\OpenCart\www1\admin\model\module 2> nul
mkdir D:\Projecten\Acumulus\OpenCart\www1\catalog\model\module 2> nul

rem Copy files in our folder structure to development installation.
setlocal enabledelayedexpansion
pushd acumulus
for %%D in (admin catalog vqmod) do (
pushd %%D
for /R %%F in (*) do (
  set B=%%F
  del D:\Projecten\Acumulus\OpenCart\www1\!B:%CD%\=!
  mklink /H D:\Projecten\Acumulus\OpenCart\www1\!B:%CD%\=! D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\!B:%CD%\=!
)
popd
)
popd
setlocal disabledelayedexpansion

rmdir /s /q D:\Projecten\Acumulus\OpenCart\www1\system\library\siel
mklink /J D:\Projecten\Acumulus\OpenCart\www1\system\library\siel D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\system\library\siel
