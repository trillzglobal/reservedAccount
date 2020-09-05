RESERVED ACCOUNT

This Document Contain Payant Connect and Monnify for Reserve Account.
Adjustment should be made for personal Use.
The Uniform Bankend resides on its own Server and Need No adjustment.
UniformBackend is created so that solution can be provided for multiple application your organization controls, multiple banks and providers can be implemented.

Reserved Account - This works by creating dedicated account Number for Customers and allowing them fund their wallet transfering from their respective bank and their account get credited via webhook communication.

Uniform Backend to Handle Process

------------------------------
Payant Account (Sterling Bank)
------------------------------
payant -|
		|-reserveAccount-|
				   		 |- index.php  //Script to call for account Reservation. 
payant -|
		|-	masterWebhook -|
				   		   |- webhook  -|
				   						|- index.php  //Connection to Payant Connect Webhook. Link used on payant dashboard for webhook communication

-------------------------------
Monnify Account (Providus Bank)
-------------------------------
monnify-|
		|-reserveAccount-|
				   		 |- index.php  //Script to call for account Reservation. 
monnify-|
		|-	masterWebhook -|
				   		   |- webhook  -|
				   						|- index.php  //Connection to Monnify  Webhook. Link used on monnify Dashboard of webhook Communication

--------------------
General Function
--------------------
functions  -|
			|- db_inc.php  //Database Connection. 
			|- func_inc.php //Functions connecting to Database and API communications
			|- variables.php //Contains All Vairable Needed for Communication
			|- dbase.sql //Database Structure for UniformBackend

-------------------------------------
Sample Account Creation and Crediting
-------------------------------------
communication	-|
				 |-insert.php //Use to create Generate Customer Account Number Calling UniformBackend
				 |-index.php //Webhook for treating communication from UniformBackend on application


