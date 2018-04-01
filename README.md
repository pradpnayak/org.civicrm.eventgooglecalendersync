# CiviCRM Event sync with Google Calendar  #

### Overview ###

CiviCRM Event can be integrated with Google Calendar.

### Prerequisites ###
1. Have your Admin SDK and Google Calendar api enabled in https://console.developers.google.com
![Screenshot of google admin sdk](images/1.png)
2. Create Google app and have your client ID and client secret
![Screenshot of google cred-1](images/2.png)
![Screenshot of google cred-2](images/4.png)
![Screenshot of google cred-3](images/6.png)
3. If your CiviCRM is already connected to Google app, you have to delete the access token in setting table in order to connect to new Google App.

### Installation ###

* Install the extension manually in CiviCRM. More details [here](http://wiki.civicrm.org/confluence/display/CRMDOC/Extensions#Extensions-Installinganewextension) about installing extensions in CiviCRM.
* Configure Google Auth details in Administer >> System Settings >> Googlecal Settings(civicrm/googlecal/settings?reset=1)
![Screenshot of civicrm setting](images/civi_google_group_setting.png)
![Screenshot of civicrm google connect](images/3.png)
