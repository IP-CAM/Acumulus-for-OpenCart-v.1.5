@echo off
rem Link Common library to here.
mkdir D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\system\library\siel 2> nul
rmdir /s /q D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\system\library\siel\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\system\library\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus

rem Link license files to here.
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\license.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\licentie-nl.pdf 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\leesmij.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt

rem Link catalog model to admin model.
mkdir D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\catalog\model\module 2> nul
del D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\catalog\model\module\acumulus.php 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\catalog\model\module\acumulus.php D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\admin\model\module\acumulus.php
