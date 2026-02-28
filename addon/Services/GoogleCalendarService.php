<?php

namespace Addon\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Google_Service_Calendar_EventAttendee;

class GoogleCalendarService
{
  private Google_Client $client;
  private ?Google_Service_Calendar $service = null;

  public function __construct()
  {
    $this->client = new Google_Client();
    // Sesuaikan path ke file JSON Service Account Anda
    $this->client->setAuthConfig(env('GOOGLE_AUTH_CONFIG', __DIR__ . '/../../storage/secrets/broadcast-agenda-kampus-597196c77bd7.json'));
    $this->client->addScope(Google_Service_Calendar::CALENDAR);
  }

  /**
   * Impersonate akun Google Workspace (Super Admin atau Organizer)
   */
  public function impersonate(string $email): self
  {
    $this->client->setSubject($email);
    $this->service = new Google_Service_Calendar($this->client);
    return $this;
  }

  /**
   * Helper private untuk membangun objek Event Google
   */
  private function buildEventObject(array $data, ?Google_Service_Calendar_Event $event = null): Google_Service_Calendar_Event
  {
    if (!$event) {
      $event = new Google_Service_Calendar_Event();
    }

    if (isset($data['title'])) $event->setSummary($data['title']);
    if (isset($data['description'])) $event->setDescription($data['description']);
    if (isset($data['location'])) $event->setLocation($data['location']);

    if (isset($data['start_time'])) {
      $start = new Google_Service_Calendar_EventDateTime();
      $start->setDateTime($data['start_time']);
      $event->setStart($start);
    }

    if (isset($data['end_time'])) {
      $end = new Google_Service_Calendar_EventDateTime();
      $end->setDateTime($data['end_time']);
      $event->setEnd($end);
    }

    // MAP ATTENDEES (Tanpa Looping API, hanya menyiapkan format array untuk Google)
    if (isset($data['attendees']) && is_array($data['attendees'])) {
      $attendeesArray = [];
      foreach ($data['attendees'] as $att) {
        $attendee = new Google_Service_Calendar_EventAttendee();
        $attendee->setEmail($att['email']);
        $attendeesArray[] = $attendee;
      }
      $event->setAttendees($attendeesArray);
    }

    return $event;
  }

  /**
   * INSERT: Membuat event baru
   */
  public function insertEvent(array $data, array $optParams = []): string
  {
    $event = $this->buildEventObject($data);
    $createdEvent = $this->service->events->insert('primary', $event, $optParams);
    return $createdEvent->getId();
  }

  /**
   * UPDATE: Mengubah event yang sudah ada (Menggunakan PATCH agar bisa parsial)
   */
  public function updateEvent(string $eventId, array $data, array $optParams = []): string
  {
    // Build event object hanya dengan data yang dikirimkan (parsial)
    $event = $this->buildEventObject($data);
    $updatedEvent = $this->service->events->patch('primary', $eventId, $event, $optParams);
    return $updatedEvent->getId();
  }

  /**
   * DELETE: Menghapus event
   */
  public function deleteEvent(string $eventId, array $optParams = []): bool
  {
    $this->service->events->delete('primary', $eventId, $optParams);
    return true;
  }

  public function listEvents(string $timeMin = 'now', int $maxResults = 10): array
  {
    if (!$this->service) {
      throw new \Exception("Please call impersonate() first.");
    }

    if ($timeMin === 'now') {
      $timeMin = date('c'); // Waktu saat ini
    }

    $params = [
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => $timeMin,
      'maxResults' => $maxResults,
    ];

    $events = $this->service->events->listEvents('primary', $params);

    $results = [];
    foreach ($events->getItems() as $event) {
      $results[] = [
        'id' => $event->getId(),
        'summary' => $event->getSummary(),
        'start' => $event->getStart()->dateTime ?? $event->getStart()->date,
        'end' => $event->getEnd()->dateTime ?? $event->getEnd()->date,
        'link' => $event->getHtmlLink()
      ];
    }

    return $results;
  }
}
