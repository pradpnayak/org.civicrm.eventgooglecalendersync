<?php

class CRM_Eventgooglecalendersync_Utils {

  /**
   * Function to connect Google.
   *
   * @return obj
   * @access public
   */
  static function googleClient() {
    $clientKey = self::getSettingValue('gc_client_key');
    $secretKey = self::getSettingValue('gc_client_secret');
    $client = new Google_Client();
    $client->setClientId($clientKey);
    $client->setClientSecret($secretKey);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->setScopes(implode(' ', array(
      Google_Service_Calendar::CALENDAR)
    ));
    return $client;
  }

  /**
   * Function to create/update event in Google Calendar.
   *
   * @param int $eventId
   *
   * @return array
   * @access public
   */
  static function createGCalEvent($eventId) {
    $client = self::googleClient();
    $accessToken = self::getSettingValue('gc_access_token');
    if ($accessToken) {
      $client->refreshToken($accessToken);
      $service = new Google_Service_Calendar($client);
      $eventParams = self::buildEventParams($eventId);
      if (!empty($eventParams)) {
        $event = new Google_Service_Calendar_Event($eventParams);
        $calendarId = 'primary';
        $gEventId = CRM_Core_DAO::singleValueQuery("
          SELECT g_event_id FROM civicrm_google_event WHERE c_event_id = {$eventId}
        ");
        if (empty($gEventId)) {
          $event = $service->events->insert($calendarId, $event);
          $gEventId = $event->getId();
          CRM_Core_DAO::executeQuery("
            INSERT INTO civicrm_google_event (g_event_id, c_event_id)
              VALUES('$gEventId', $eventId)
          ");
        }
        else {
          $event = $service->events->update($calendarId, $gEventId, $event);
        }
      }
    }
  }

  /**
   * Function to build params.
   *
   * @param int $eventId
   *
   * @return array
   * @access public
   */
  static function buildEventParams($eventId) {
    $events = civicrm_api3('event', 'getsingle', ['id' => $eventId]);
    if (empty($events['is_public'])
      || empty($events['is_active'])
      || empty($events['end_date'])
    ) {
      return NULL;
    }
    $eventParams = [
      'summary' => $events['title'],
      'colorId' => $events['event_type_id'] % 10,
      'description' => $events['description'],
    ];
    $timeZone = date_default_timezone_get();
    $start = date('Y-m-d\TH:i:s', strtotime($events['start_date']));
    $end = date('Y-m-d\TH:i:s', strtotime($events['end_date']));
    $eventParams['start'] = new Google_Service_Calendar_EventDateTime();
    $eventParams['start']->setDateTime($start);
    $eventParams['start']->setTimeZone($timeZone);
    $eventParams['end'] = new Google_Service_Calendar_EventDateTime();
    $eventParams['end']->setDateTime($end);
    $eventParams['end']->setTimeZone($timeZone);
    $eventParams['timezone'] = $timeZone;
    return $eventParams;
  }

  /**
   * Function retrieve values from civicrm_setting using api.
   *
   * @param string $settingName
   */
  public static function getSettingValue($settingName) {
    try {
      $setting = civicrm_api3('Setting', 'getvalue', [
        'name' => $settingName,
      ]);
      if ($settingName == 'cg_domain_name') {
        return explode(',', $setting);
      }
      return $setting;
    }
    catch (CiviCRM_API3_Exception $e) {
      return NULL;
    }
  }
}
