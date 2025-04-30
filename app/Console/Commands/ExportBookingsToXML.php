<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportBookingsToXML extends Command
{
    protected $signature = 'export:bookings-xml';
    protected $description = 'Export bookings table to XML';

    public function handle()
    {
        $bookings = DB::table('bookings')->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><bookings></bookings>');

        foreach ($bookings as $booking) {
            $bookingXml = $xml->addChild('booking');
            foreach ((array) $booking as $key => $value) {
                $bookingXml->addChild($key, htmlspecialchars($value));
            }
        }

        // 存储在 storage/app/public/bookings.xml
        Storage::disk('public')->put('bookings.xml', $xml->asXML());

        $this->info('Bookings exported to XML successfully.');
    }
}
