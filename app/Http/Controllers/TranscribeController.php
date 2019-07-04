<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use AWS;

class TranscribeController extends Controller
{
	public function transcribe(Request $request) {
		$client = AWS::createClient('transcribeService');
		$path = 'https://kuflink.s3.eu-west-2.amazonaws.com/voipbuy.com-1562067652.120-1-724000023-1562067652.mp3';
		try {
			$result = $client->getTranscriptionJob([
				'TranscriptionJobName' => 'test_transcribe'
			]);
			if ($result['TranscriptionJob']['TranscriptionJobStatus'] == 'IN_PROGRESS') {
				return redirect('/')->with('status', 'Progressing now');
			} else if ($result['TranscriptionJob']['TranscriptionJobStatus'] == 'COMPLETED') {
				$file = file_get_contents($result['TranscriptionJob']['Transcript']['TranscriptFileUri']);
				$json = json_decode($file);
				$transcript = $json->results->transcripts[0]->transcript;
				$client->deleteTranscriptionJob([
				    'TranscriptionJobName' => 'test_transcribe', // REQUIRED
				]);
				return redirect('/')->with('result', $transcript);
			}
		} catch (Aws\TranscribeService\Exception\TranscribeServiceException $e) {
			$result = $client->startTranscriptionJob([
			    'LanguageCode' => 'en-GB', // REQUIRED
			    'Media' => [ // REQUIRED
			        'MediaFileUri' => $path,
			    ],
			    'MediaFormat' => 'mp3', // REQUIRED
			    'TranscriptionJobName' => 'test_transcribe', // REQUIRED
			]);
			return redirect('/')->with('status', 'Progressing now');
		}
	}
}