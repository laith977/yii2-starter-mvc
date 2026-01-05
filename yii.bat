@echo off
REM Windows entry point for Yii console commands

setlocal DISABLEDELAYEDEXPANSION
php "%~dp0yii" %*

