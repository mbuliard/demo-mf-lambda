<?php

namespace App\Sqs;

use Aws\Sqs\SqsClient;

class Publisher
{
    private SqsClient $client;
    private string $queueUrl;

    public function __construct(SqsClient $client, string $queueUrl)
    {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
    }

    public function publish(mixed $message, array $messageAttributes = [], array $options = []): string
    {
        $body = is_string($message) ? $message : json_encode($message, JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            throw new \RuntimeException('Failed to JSON encode message: ' . json_last_error_msg());
        }

        $params = array_merge([
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $body,
        ], $options);

        if (!empty($messageAttributes)) {
            $params['MessageAttributes'] = $messageAttributes;
        }

        try {
            $result = $this->client->sendMessage($params);

            $messageId = $result->get('MessageId');

            return is_string($messageId) ? $messageId : (string) $messageId;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to publish message to SQS: ' . $e->getMessage(), 0, $e);
        }
    }
}
