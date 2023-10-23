<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Activity plugin for lytix
 *
 * @package   lytix_activity
 * @author    Günther Moser <moser@tugraz.at>
 * @author    Viktoria Wieser <viktoria.wieser@tugraz.at>
 * @copyright 2021 Educational Technologies, Graz, University of Technology
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Lytix Activity';
$string['privacy:metadata'] = 'This plugin does not store any data.';

// Activity.
$string['activity'] = 'Aktivitätsgraph';
$string['error_text'] = '<div class="alert alert-danger">Etwas ist schiefgegangen, bitte Seite neu laden(F5). <br>
 Falls dieser Fehler dann noch immer passiert, wenden Sie sich bitte an Ihren Administrator.</div>';
$string['overall_time'] = 'Gesamtzeit';
$string['time_per_day'] = 'Zeit pro Tag';
$string['show_others'] = 'andere Studierende';
$string['average_all'] = "durchschnitt Gesamt";
$string['user_all'] = "ich Gesamt";
$string['all_core'] = "durchnitt im Kurs";
$string['user_core'] = "ich im Kurs";
$string['all_forum'] = "durchnitt im Forum";
$string['user_forum'] = "ich im Forum";
$string['all_grade'] = "durchnitt in Bewertung";
$string['user_grade'] = "ich in Bewertung";
$string['all_submission'] = "durchnitt für Abgaben";
$string['user_submission'] = "ich für Abgaben";
$string['all_resource'] = "durchnitt für Ressourcen";
$string['user_resource'] = "ich für Ressourcen";
$string['all_quiz'] = "durchnitt für Quizze";
$string['user_quiz'] = "ich für Quizze";
$string['all_bbb'] = "durchnittich in BBB";
$string['user_bbb'] = "ich in BBB";
$string['no_activities_found'] = "Keine Aktivitäten in für diesen Kurs gefunden.";
$string['sum_user'] = "Summe Benutzer: ";
$string['sum_average'] = "Summe Durchschnitt: ";
$string['core'] = "im Kurs";
$string['forum'] = "Forum";
$string['grade'] = "Note";
$string['submission'] = "Aufgabe";
$string['resource'] = "Ressource";
$string['quiz'] = "Quiz";
$string['video'] = "Video";
$string['bbb'] = "BigBlueButton";
$string['all'] = 'Alle';
$string['nodata'] = 'zu wenig Daten vorhanden';
$string['description_me'] = 'Hier ist abgebildet, wie viel Zeit Sie durchschnittlich mit welchen Aktivitäten verbringen.';
$string['description_others'] = 'Das sind die durchschnittlichen Zeiten der anderen.';
$string['h'] = 'Stunden';
$string['m'] = 'Minuten';
$string['s'] = 'Sekunden';
$string['title'] = 'Aktivität';
// Privacy.
$string['privacy:metadata:local_coursebackupexport'] = "Um das Verhalten von Personen im Kurs zu überwachen,\
 müssen etliche Benutzerdaten gespeichert werden";
$string['privacy:metadata:local_coursebackupexport:courseid'] = "Die Kursnummer wird gespeichert, um nachvollziehen\
 zu können, von welchem Kurs die Daten erhoben wurden";
$string['privacy:metadata:local_coursebackupexport:userid'] = "Die Benutzernummer wird gespeichert, um die Person,\
 die den Kurs besucht hat, identifizieren zu können";
$string['privacy:metadata:lytix_activity:show_others'] = "Option um die Aktivitäten der andren Kursteilnehmer sehen\
 zu können wird hier gespeichert";
$string['privacy:metadata:lytix_activity:future'] = "Dieses Feld ist ein Platzhalter für mögliche neue Werte";
