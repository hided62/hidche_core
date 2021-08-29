import $ from 'jquery';
import { exportWindow } from './util/exportWindow';
import { mb_strwidth } from './util/mb_strwidth';
import { unwrap_any } from './util/unwrap_any';

declare const defaultStatTotal: number;
declare const defaultStatMax: number;
declare const defaultStatMin: number;
declare const charInfoText: Record<string, string>;
$(function ($) {
    const $leadership = $('#leadership');
    const $strength = $('#strength');
    const $intel = $('#intel');

    function abilityRand(): void {
        let leadership = Math.random() * 65 + 10;
        let strength = Math.random() * 65 + 10;
        let intel = Math.random() * 65 + 10;
        const rate = leadership + strength + intel;

        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);


        while (leadership + strength + intel < defaultStatTotal) {
            leadership += 1;
        }

        if (leadership > defaultStatMax || strength > defaultStatMax || intel > defaultStatMax || leadership < defaultStatMin || strength < defaultStatMin || intel < defaultStatMin) {
            return abilityRand();
        }

        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }


    function abilityLeadpow() {
        let leadership = Math.random() * 6;
        let strength = Math.random() * 6;
        let intel = Math.random() * 1;
        const rate = leadership + strength + intel;

        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);

        while (leadership + strength + intel < defaultStatTotal) {
            strength += 1;
        }

        if (intel < defaultStatMin) {
            leadership -= defaultStatMin - intel;
            intel = defaultStatMin;
        }

        if (leadership > defaultStatMax) {
            strength += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }

        if (strength > defaultStatMax) {
            leadership += strength - defaultStatMax;
            strength = defaultStatMax;
        }

        if (leadership > defaultStatMax) {
            intel += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }

        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }

    function abilityLeadint() {
        let leadership = Math.random() * 6;
        let strength = Math.random() * 1;
        let intel = Math.random() * 6;
        const rate = leadership + strength + intel;

        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);

        while (leadership + strength + intel < defaultStatTotal) {
            intel += 1;
        }

        if (strength < defaultStatMin) {
            leadership -= defaultStatMin - strength;
            strength = defaultStatMin;
        }

        if (leadership > defaultStatMax) {
            intel += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }

        if (intel > defaultStatMax) {
            leadership += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if (leadership > defaultStatMax) {
            strength += leadership - defaultStatMax;
            leadership = defaultStatMax;
        }

        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }

    function abilityPowint() {
        let leadership = Math.random() * 1;
        let strength = Math.random() * 6;
        let intel = Math.random() * 6;
        const rate = leadership + strength + intel;

        leadership = Math.floor(leadership / rate * defaultStatTotal);
        strength = Math.floor(strength / rate * defaultStatTotal);
        intel = Math.floor(intel / rate * defaultStatTotal);

        while (leadership + strength + intel < defaultStatTotal) {
            intel += 1;
        }

        if (leadership < defaultStatMin) {
            strength -= defaultStatMin - leadership;
            leadership = defaultStatMin;
        }

        if (strength > defaultStatMax) {
            intel += strength - defaultStatMax;
            strength = defaultStatMax;
        }

        if (intel > defaultStatMax) {
            strength += intel - defaultStatMax;
            intel = defaultStatMax;
        }

        if (strength > defaultStatMax) {
            leadership += strength - defaultStatMax;
            strength = defaultStatMax;
        }

        $leadership.val(leadership);
        $strength.val(strength);
        $intel.val(intel);
    }

    exportWindow(abilityRand, 'abilityRand');
    exportWindow(abilityLeadpow, 'abilityLeadpow');
    exportWindow(abilityLeadint, 'abilityLeadint');
    exportWindow(abilityPowint, 'abilityPowint');

    const $charInfoText = $('#charInfoText');
    const $selChar = $('#selChar');
    $selChar.change(function () {
        const $this = $(this);
        const char = unwrap_any<string>($this.val());
        if (char in charInfoText) {
            $charInfoText.html(charInfoText[char]);
        }
        else {
            $charInfoText.html('');
        }
    });

    const $generalName = $('#generalName');
    $generalName.on('change keyup paste', function () {
        const generalName = unwrap_any<string>($generalName.val());
        const len = mb_strwidth(generalName);
        if (len == 0 || len > 18) {
            $generalName.css('color', 'red');
        }
        else {
            $generalName.css('color', 'white');
        }
    });

    $('#join_form').submit(function () {
        const generalName = unwrap_any<string>($generalName.val());
        if (mb_strwidth(generalName) > 18) {
            alert('장수 이름이 너무 깁니다!');
            return false;
        }
        const currentStatTotal = parseInt(unwrap_any<string>($leadership.val())) + parseInt(unwrap_any<string>($strength.val())) + parseInt(unwrap_any<string>($intel.val()));
        if (currentStatTotal < defaultStatTotal) {
            if (!confirm(`현재 능력치 총합은 ${currentStatTotal}으로, ${defaultStatTotal}보다 낮습니다. 그래도 생성할까요?`)) {
                return false;
            }
        }
        return true;
    });

    const randomGenType = Math.floor(Math.random() * 7);
    if (randomGenType < 3) {
        abilityLeadpow();
    }
    else if (randomGenType < 6) {
        abilityLeadint();
    }
    else {
        abilityPowint();
    }

});