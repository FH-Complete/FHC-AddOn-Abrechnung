<?php
require_once(dirname(__FILE__).'/../../../../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../../include/abrechnung.class.php');

class BerechneTageTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

	/**
	 * Wenn die erste Abrechnung am 31. stattfindet muss 1 geliefert werden
	 */
    public function testTag31()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(1,$abrechnung->BerechneGesamtTage('2015-08-31','2015-08-31', '2016-02-01'));
    }

      /**
         * Wenn die erste Abrechnung am 30. stattfindet und letzter Tag im Monat muss 1 geliefert werden
         */
    public function testTag30()
    {
                $abrechnung = new abrechnung();
                $this->assertEquals(1,$abrechnung->BerechneGesamtTage('2015-09-30','2015-09-30', '2016-02-01'));
    }

	/**
	 * Volles Monat = 30 Tage
	 */
    public function testVollesMonat()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(30,$abrechnung->BerechneGesamtTage('2015-09-01','2015-09-30', '2016-02-01'));
    }

	/**
	 * Erstes Monat wird immer bis Monatsende gerechnet nicht bis 30.
	 */
    public function testVollesMonat31First()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(31,$abrechnung->BerechneGesamtTage('2015-10-01','2015-10-31', '2016-02-01'));
    }

	/**
	 * Monate die dazwischen liegen werden immer mit 30 gerechnet
	 */
    public function testVollesMonat31Mitte()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(90,$abrechnung->BerechneGesamtTage('2015-09-01','2015-11-30', '2016-02-01'));
    }

	/**
	 * Wenn der Februar ein Zwischenmonat ist, wird er auch mit 30 Tagen gerechnet
	 */
    public function testFebruarNichtEnde()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(61,$abrechnung->BerechneGesamtTage('2016-01-01','2016-02-29', '2016-03-05'));
    }

	/**
	 * Wenn der Februar der letzte Monat ist wird dieser mit 29/28 Tagen gerechnet
	 */
    public function testFebruarEnde()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(60,$abrechnung->BerechneGesamtTage('2016-01-01','2016-02-29', '2016-02-29'));
    }

	/**
	 * normale Tagesberechnung
	 */
    public function testMonatsmitte()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(23,$abrechnung->BerechneGesamtTage('2015-09-08','2015-09-30', '2016-03-05'));
    }

	/**
	 * 1. Monat normal zwischenmonat mit 30
	 */
    public function testMehrmonate()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(53,$abrechnung->BerechneGesamtTage('2015-09-08','2015-10-31', '2016-03-05'));
    }

	/**
	 * Berechnung ueber gesamtes Semester
	 */
	public function testGesamtsemester()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(151,$abrechnung->BerechneGesamtTage('2015-08-31','2016-01-31', '2016-01-31'));
    }

	public function testMonatsabrechnung()
    {
		$abrechnung = new abrechnung();
		$this->assertEquals(31,$abrechnung->BerechneGesamtTage('2015-10-01','2015-10-31', '2016-01-31'));
    }
}
