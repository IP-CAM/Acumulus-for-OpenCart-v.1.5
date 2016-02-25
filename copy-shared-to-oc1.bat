@echo off
rem Link Common library to here.
mklink /J D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\system\library\Siel D:\Projecten\Acumulus\Webkoppelingen\Library\Siel

rem Link catalog model to admin model.
mkdir D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\catalog\model\module
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\catalog\model\module\acumulus.php D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\admin\model\module\acumulus.php

rem Link license files to here.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\changelog.txt D:\Projecten\Acumulus\Webkoppelingen\changelog-4.x.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\license.txt D:\Projecten\Acumulus\Webkoppelingen\license.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\licentie-nl.pdf
mklink /H D:\Projecten\Acumulus\Webkoppelingen\OpenCart1\acumulus\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt
