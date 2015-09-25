<?php
namespace Rocket\Mail;

class Mail
{
	private $from = false;
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $subject = '';
	private $replyTo = array();
	private $contentType = 'text';
	private $html = false;
	private $alt = false;
	private $multipartIntro = 'This is a multipart message.';
	private $attachments = array();
	private $embedded = array();
	const CLRF = "\r\n";

	public function __construct()
	{

	}

	public function from($email, $name = false)
	{
		$this->from = ($name ? $name . ' ' : '') . '<' . $email . '>';
		return $this;
	}

	public function to($email, $name = false)
	{
		$this->to = array();
		$this->addTo($email, $name);
		return $this;
	}

	public function addTo($email, $name = false)
	{
		$this->to[] = ($name ? $name . ' ' : '') . '<' . $email . '>';
		return $this;
	}

	public function cc($email, $name = false)
	{
		$this->cc = array();
		$this->addCc($email, $name);
		return $this;
	}

	public function addCc($email, $name = false)
	{
		$this->cc[] = ($name ? $name . ' ' : '') . '<' . $email . '>';
		return $this;
	}

	public function bcc($email, $name = false)
	{
		$this->bcc = array();
		$this->addBcc($email, $name);
		return $this;
	}

	public function addBcc($email, $name = false)
	{
		$this->bcc[] = ($name ? $name . ' ' : '') . '<' . $email . '>';
		return $this;
	}

	public function subject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	public function message($html, $alt = false)
	{
		if ($alt !== false){
			$this->html = $html;
			$this->alt = $alt;
		}
		return $this;
	}

	public function html($html)
	{
		$this->html = $html;
	}

	public function alt($alt)
	{
		$this->alt = $alt;
	}

	private function parseFile($file, $as = false, $mime = false)
	{
		if (!file_exists($file)){
			return false;
		}

		// if no $as then default to filename
		$as = $as ? $as : basename($file);

		// if no mime defined by user, detect it
		if (!$mime){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
		}

		return array(
			'file' => $file,
			'as' => $as,
			'mime' => $mime
		);
	}

	public function attach($file, $as = false, $mime = false)
	{
		$attachment = $this->parseFile($file, $as, $mime);
		if ($attachment){
			$this->attachments[] = $attachment;
		}
		return $this;
	}

	public function embed($file, $as = false, $mime = false)
	{
		$embed = $this->parseFile($file, $as, $mime);
		if ($embed){
			$this->embedded[] = $embed;
		}
		return $this;
	}

	public function send()
	{
		if (count($this->to) == 0){
			return false;
		}

		$boundary = 'multipart_boundary_' . time();

		$headers = array();
		$headers[]  = "From: " . $this->from;
		$headers[] .= "Reply-To: ". $this->from;
		$headers[] .= "cc: " . implode(',', $this->cc);
		$headers[] .= "Bcc: " . implode(',', $this->bcc);
		$headers[] .= "MIME-Version: 1.0";
		if (count($this->attachments) || count($this->embedded)){
			// has attachments
			$headers[] .= 'Content-Type: multipart/mixed;boundary="'.$boundary.'"';
		}else if ($this->html && $this->alt){
			// is multipart
			$headers[] .= 'Content-Type: multipart/alternative;boundary="'.$boundary.'"';
		}else if ($this->html){
			// is html
			$headers[] .= "Content-Type: text/html; charset=UTF-8";
		}else{
			// is plain text
			$headers[] .= "Content-Type: text/plain; charset=UTF-8";
		}


		// TODO: build message according to header
		$message = array();
		$message[] = $this->multipartIntro . self::CLRF;
		$message[] = '--'.$boundary; // start plain text message
		$message[] = 'Content-Type: text/plain; charset=UTF-8';
		$message[] = 'Content-Transfer-Encoding: 8bit';
		$message[] = self::CLRF . wordwrap($this->alt, 70, self::CLRF) . self::CLRF;
		$message[] = '--'.$boundary; // start html message
		$message[] = 'Content-Type: text/html; charset=UTF-8';
		$message[] = 'Content-Transfer-Encoding: 8bit';
		$message[] = self::CLRF . wordwrap($this->html, 70, self::CLRF) . self::CLRF;


		foreach ($this->attachments as $attachment){
			$message[] = '--'.$boundary; // start attachment
			$message[] = 'Content-Type: '.$attachment['mime'].'; name="'.$attachment['as'].'"';
			$message[] = 'Content-Transfer-Encoding: base64';
			$message[] = 'Content-Disposition: attachment';

			$file = fopen($attachment['file'],'rb');
			$data = fread($file, filesize($attachment['file']));
			fclose($file);
			$data = chunk_split(base64_encode($data));
			$message[] = $data;
		}

		foreach ($this->embedded as $embed){
			$message[] = '--'.$boundary; // start embedded
			$message[] = 'Content-Type: '.$embed['mime'].'; name="'.$embed['as'].'"';
			$message[] = 'Content-Transfer-Encoding: base64';
			$message[] = 'Content-ID: <'.$embed['as'].'>';
			$message[] = 'Content-Disposition: inline';

			$file = fopen($embed['file'],'rb');
			$data = fread($file, filesize($embed['file']));
			fclose($file);
			$data = chunk_split(base64_encode($data));
			$message[] = $data;
		}

		$message[] = '--'.$boundary.'--'; // end the message

		return mail(implode(',', $this->to), $this->subject, implode(self::CLRF, $message), implode(self::CLRF, $headers));
	}
}