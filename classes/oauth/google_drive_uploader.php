<?php
/**
 * Cliente mínimo Google Drive API v3 (OAuth2 + subida multipart).
 *
 * @package   local_scheduled_backup_cloud
 */

namespace local_scheduled_backup_cloud\oauth;

defined('MOODLE_INTERNAL') || die();

/**
 * Subida y carpetas en Drive usando refresh_token almacenado en config.
 */
class google_drive_uploader {

    /** @var string */
    protected $clientid;
    /** @var string */
    protected $clientsecret;
    /** @var string */
    protected $refreshtoken;

    public function __construct(string $clientid, string $clientsecret, string $refreshtoken) {
        $this->clientid = $clientid;
        $this->clientsecret = $clientsecret;
        $this->refreshtoken = $refreshtoken;
    }

    /**
     * Obtiene access_token.
     */
    public function get_access_token(): ?string {
        if ($this->refreshtoken === '') {
            return null;
        }
        $params = [
            'client_id' => $this->clientid,
            'client_secret' => $this->clientsecret,
            'refresh_token' => $this->refreshtoken,
            'grant_type' => 'refresh_token',
        ];
        $curl = new \curl();
        $response = $curl->post('https://oauth2.googleapis.com/token', $params);
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['access_token'])) {
            debugging('local_scheduled_backup_cloud: token refresh failed: ' . $response, DEBUG_DEVELOPER);
            return null;
        }
        return (string) $data['access_token'];
    }

    /**
     * Crea carpeta bajo un padre (o 'root').
     */
    public function ensure_folder(string $access, string $parentid, string $title): ?string {
        $safetitle = str_replace(["\\", "'"], ["\\\\", "\\'"], $title);
        $safeparent = str_replace(["\\", "'"], ["\\\\", "\\'"], $parentid);
        $q = "'" . $safeparent . "' in parents and mimeType = 'application/vnd.google-apps.folder' and name = '" .
            $safetitle . "' and trashed = false";

        $listurl = 'https://www.googleapis.com/drive/v3/files?' . http_build_query([
            'q' => $q,
            'fields' => 'files(id,name)',
            'pageSize' => 10,
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ]);

        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $access);
        $found = $curl->get($listurl);
        $json = json_decode($found, true);
        if (is_array($json) && !empty($json['files'][0]['id'])) {
            return (string) $json['files'][0]['id'];
        }

        $payload = json_encode([
            'name' => $title,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentid],
        ]);

        $curl = new \curl();
        $curl->setHeader('Authorization: Bearer ' . $access);
        $curl->setHeader('Content-Type: application/json; charset=UTF-8');
        $created = $curl->post('https://www.googleapis.com/drive/v3/files?' . http_build_query([
            'fields' => 'id',
            'supportsAllDrives' => 'true',
        ]), $payload);

        $out = json_decode($created, true);
        if (is_array($out) && !empty($out['id'])) {
            return (string) $out['id'];
        }
        debugging('local_scheduled_backup_cloud: create folder failed: ' . $created, DEBUG_DEVELOPER);
        return null;
    }

    /**
     * Resuelve cadena de carpetas bajo root.
     */
    public function ensure_path(string $access, array $segments): ?string {
        $parent = 'root';
        foreach ($segments as $seg) {
            $seg = trim($seg);
            if ($seg === '') {
                continue;
            }
            $next = $this->ensure_folder($access, $parent, $seg);
            if ($next === null) {
                return null;
            }
            $parent = $next;
        }
        return $parent;
    }

    /**
     * Sube un archivo al folder indicado (multipart/related).
     */
    public function upload_file(string $access, string $folderid, string $filepath, string $filename): bool {
        if (!is_readable($filepath)) {
            return false;
        }
        $meta = json_encode([
            'name' => $filename,
            'parents' => [$folderid],
        ]);
        $filecontent = file_get_contents($filepath);
        if ($filecontent === false) {
            return false;
        }
        $boundary = 'moodle_scheduledbk_' . random_int(100000, 999999);
        $body = '--' . $boundary . "\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= $meta . "\r\n";
        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Type: application/octet-stream\r\n\r\n";
        $body .= $filecontent . "\r\n";
        $body .= '--' . $boundary . '--';

        $url = 'https://www.googleapis.com/upload/drive/v3/files?' . http_build_query([
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => 'true',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $access,
                'Content-Type: multipart/related; boundary=' . $boundary,
            ],
            CURLOPT_POSTFIELDS => $body,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        if ($resp === false) {
            return false;
        }
        $data = json_decode($resp, true);
        return is_array($data) && !empty($data['id']);
    }
}
