<?php

namespace App\Business\CollectionTransforms;

use App\Models\Tenant\NotifyHistory;

class NotifyHistoryTransform
{
    public static function transform(NotifyHistory $item): array
    {
        $sentTo = [];
        if (isset($item->JSONData['To']['Galas'])) {
            foreach ($item->JSONData['To']['Galas'] as $key => $name) {
                $sentTo[] = [
                    'id' => $key,
                    'name' => $name,
                    'type' => 'gala',
                ];
            }
        }

        if (isset($item->JSONData['To']['Squads'])) {
            foreach ($item->JSONData['To']['Squads'] as $key => $name) {
                $sentTo[] = [
                    'id' => $key,
                    'name' => $name,
                    'type' => 'squad',
                ];
            }
        }

        if (isset($item->JSONData['To']['Targeted_Lists'])) {
            foreach ($item->JSONData['To']['Targeted_Lists'] as $key => $name) {
                $sentTo[] = [
                    'id' => $key,
                    'name' => $name,
                    'type' => 'targeted_list',
                ];
            }
        }

        $routeName = tenant() ? 'notify.email.download_file' : 'central.notify.download_file';

        $attachments = [];
        if (isset($item->JSONData['Attachments'])) {
            foreach ($item->JSONData['Attachments'] as $key => $data) {
                $attachments[] = [
                    'key' => $key,
                    'name' => $data['Filename'],
                    'mime_type' => $data['MIME'],
                    'path' => $data['URI'],
                    'url' => route($routeName, ['email' => $item->ID, 'file' => $data['URI']]),
                ];
            }
        }

        return [
            'id' => $item->ID,
            'subject' => $item->Subject,
            'message' => $item->Message,
            'tenant' => [
                'id' => $item->tenant->id,
                'name' => $item->tenant->Name,
            ],
            'author' => $item->author ? [
                'first_name' => $item->author->Forename,
                'last_name' => $item->author->Surname,
                'id' => $item->author->UserID,
            ] : null,
            'force_send' => (bool)$item->JSONData['Metadata']['ForceSend'],
            'sent_as' => isset($item->JSONData['NamedSender']) ? [
                'name' => $item->JSONData['NamedSender']['Name'],
                'email' => $item->JSONData['NamedSender']['Email']
            ] : null,
            'reply_to' => isset($item->JSONData['ReplyToMe']) ? [
                'name' => $item->JSONData['ReplyToMe']['Name'],
                'email' => $item->JSONData['ReplyToMe']['Email']
            ] : null,
            'attachments' => $attachments,
            'sent_to' => $sentTo,
            'date' => $item->Date,
            'json_data' => $item->JSONData,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}
