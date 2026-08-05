<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->services() as $position => $data) {
            $requirements = $data['requirements'];
            unset($data['requirements']);

            $service = Service::query()->updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );

            $service->requirements()->delete();

            foreach ($requirements as $index => $requirement) {
                $service->requirements()->create([
                    ...$requirement,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function services(): array
    {
        $contact = [
            'contact_email' => 'help@barangayharaya.test',
            'contact_phone' => '(02) 8123 4567',
            'is_active' => true,
        ];

        return [
            [
                ...$contact,
                'slug' => 'barangay-clearance',
                'name_en' => 'Barangay Clearance',
                'name_fil' => 'Barangay Clearance',
                'description_en' => 'A demonstration clearance confirming that a resident has no recorded unresolved barangay matter for common local transactions.',
                'description_fil' => 'Isang demonstrasyong clearance na nagpapatunay na walang nakatalang hindi pa naaayos na usaping pambarangay ang residente para sa mga karaniwang transaksiyon.',
                'eligibility_en' => 'Residents of fictional Barangay Haraya who have lived in the community for at least six months.',
                'eligibility_fil' => 'Mga residente ng kathang-isip na Barangay Haraya na naninirahan sa komunidad nang hindi bababa sa anim na buwan.',
                'fee_centavos' => 5000,
                'processing_time_en' => '1 to 2 business days',
                'processing_time_fil' => '1 hanggang 2 araw ng trabaho',
                'target_business_days' => 2,
                'office_hours_en' => 'Monday to Friday, 8:00 AM to 5:00 PM',
                'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
                'procedure_steps_en' => ['Review the listed requirements.', 'Submit the request and preferred release schedule.', 'Wait for staff review and an acknowledgement.', 'Present the tracking reference when collecting the demonstration document.'],
                'procedure_steps_fil' => ['Suriin ang mga nakalistang kinakailangan.', 'Isumite ang kahilingan at gustong iskedyul ng pagkuha.', 'Hintayin ang pagsusuri at pagkilala ng kawani.', 'Ipakita ang tracking reference sa pagkuha ng demonstrasyong dokumento.'],
                'appointment_required' => false,
                'requirements' => [
                    ['name_en' => 'Proof of local address', 'name_fil' => 'Patunay ng lokal na tirahan', 'details_en' => 'For this demonstration, describe the type of proof only; do not upload a real government ID.', 'details_fil' => 'Para sa demonstrasyong ito, ilarawan lamang ang uri ng patunay; huwag mag-upload ng tunay na government ID.', 'is_required' => true],
                    ['name_en' => 'Purpose of request', 'name_fil' => 'Layunin ng kahilingan', 'details_en' => 'State the transaction where the clearance will be used.', 'details_fil' => 'Sabihin ang transaksiyon kung saan gagamitin ang clearance.', 'is_required' => true],
                ],
            ],
            [
                ...$contact,
                'slug' => 'certificate-of-residency',
                'name_en' => 'Certificate of Residency',
                'name_fil' => 'Katibayan ng Paninirahan',
                'description_en' => 'A demonstration certificate confirming a person\'s stated residence within Barangay Haraya.',
                'description_fil' => 'Isang demonstrasyong katibayan na nagpapatunay sa nakasaad na paninirahan ng isang tao sa Barangay Haraya.',
                'eligibility_en' => 'Current residents of fictional Barangay Haraya.',
                'eligibility_fil' => 'Kasalukuyang residente ng kathang-isip na Barangay Haraya.',
                'fee_centavos' => 3000,
                'processing_time_en' => '1 business day',
                'processing_time_fil' => '1 araw ng trabaho',
                'target_business_days' => 1,
                'office_hours_en' => 'Monday to Friday, 8:00 AM to 5:00 PM',
                'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
                'procedure_steps_en' => ['Confirm that you meet the eligibility guidance.', 'Provide your contact and address information.', 'Wait for staff verification.', 'Collect the demonstration certificate after a ready-for-release notice.'],
                'procedure_steps_fil' => ['Tiyaking pasok ka sa gabay sa pagiging karapat-dapat.', 'Ibigay ang iyong contact at address information.', 'Hintayin ang pagpapatunay ng kawani.', 'Kunin ang demonstrasyong katibayan matapos ang abisong maaari na itong kunin.'],
                'appointment_required' => false,
                'requirements' => [
                    ['name_en' => 'Current local address', 'name_fil' => 'Kasalukuyang lokal na tirahan', 'details_en' => 'Street or area within the fictional barangay.', 'details_fil' => 'Kalye o lugar sa loob ng kathang-isip na barangay.', 'is_required' => true],
                    ['name_en' => 'Length of residency', 'name_fil' => 'Tagal ng paninirahan', 'details_en' => 'Approximate month and year residency began.', 'details_fil' => 'Tinatayang buwan at taon ng pagsisimula ng paninirahan.', 'is_required' => true],
                ],
            ],
            [
                ...$contact,
                'slug' => 'certificate-of-indigency',
                'name_en' => 'Certificate of Indigency',
                'name_fil' => 'Katibayan ng Indigency',
                'description_en' => 'A fictional demonstration workflow for residents requesting proof of financial hardship for an eligible assistance program.',
                'description_fil' => 'Isang kathang-isip na demonstrasyong proseso para sa residenteng humihiling ng katibayan ng kahirapang pinansyal para sa angkop na programang tulong.',
                'eligibility_en' => 'Residents who can explain the assistance purpose. Do not submit medical, banking, or government identification data.',
                'eligibility_fil' => 'Mga residenteng makapagpapaliwanag ng layunin ng tulong. Huwag magsumite ng medikal, bangko, o government identification data.',
                'fee_centavos' => 0,
                'processing_time_en' => '2 to 3 business days',
                'processing_time_fil' => '2 hanggang 3 araw ng trabaho',
                'target_business_days' => 3,
                'office_hours_en' => 'Monday to Friday, 8:00 AM to 5:00 PM',
                'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
                'procedure_steps_en' => ['Read the privacy guidance before submitting.', 'Describe the assistance purpose without sensitive details.', 'Attend a short appointment if staff requests clarification.', 'Wait for the release notification.'],
                'procedure_steps_fil' => ['Basahin ang gabay sa privacy bago magsumite.', 'Ilarawan ang layunin ng tulong nang walang sensitibong detalye.', 'Dumalo sa maikling appointment kung humingi ng paglilinaw ang kawani.', 'Hintayin ang abiso sa pagkuha.'],
                'appointment_required' => true,
                'requirements' => [
                    ['name_en' => 'Assistance purpose', 'name_fil' => 'Layunin ng tulong', 'details_en' => 'A plain-language explanation without medical or financial account information.', 'details_fil' => 'Payak na paliwanag nang walang medikal o financial account information.', 'is_required' => true],
                    ['name_en' => 'Program or requesting organization', 'name_fil' => 'Programa o humihiling na organisasyon', 'details_en' => 'Name of the program, when known.', 'details_fil' => 'Pangalan ng programa, kung alam.', 'is_required' => false],
                ],
            ],
            [
                ...$contact,
                'slug' => 'barangay-business-clearance',
                'name_en' => 'Barangay Business Clearance',
                'name_fil' => 'Barangay Business Clearance',
                'description_en' => 'A demonstration local clearance workflow for a fictional small business operating within Barangay Haraya.',
                'description_fil' => 'Isang demonstrasyong lokal na clearance process para sa kathang-isip na maliit na negosyong nasa Barangay Haraya.',
                'eligibility_en' => 'Owners or authorized representatives of a fictional business address within Barangay Haraya.',
                'eligibility_fil' => 'May-ari o awtorisadong kinatawan ng kathang-isip na negosyong may address sa Barangay Haraya.',
                'fee_centavos' => 20000,
                'processing_time_en' => '3 to 5 business days',
                'processing_time_fil' => '3 hanggang 5 araw ng trabaho',
                'target_business_days' => 5,
                'office_hours_en' => 'Monday to Friday, 8:00 AM to 4:00 PM',
                'office_hours_fil' => 'Lunes hanggang Biyernes, 8:00 AM hanggang 4:00 PM',
                'procedure_steps_en' => ['Review the business information requirements.', 'Submit the fictional business details.', 'Select an inspection appointment preference.', 'Wait for review and a public status update.'],
                'procedure_steps_fil' => ['Suriin ang mga kinakailangang impormasyon ng negosyo.', 'Isumite ang kathang-isip na detalye ng negosyo.', 'Pumili ng gustong iskedyul para sa inspeksiyon.', 'Hintayin ang pagsusuri at pampublikong status update.'],
                'appointment_required' => true,
                'requirements' => [
                    ['name_en' => 'Business name and local address', 'name_fil' => 'Pangalan at lokal na address ng negosyo', 'details_en' => 'Use fictional information in this portfolio demonstration.', 'details_fil' => 'Gumamit ng kathang-isip na impormasyon sa portfolio demonstration na ito.', 'is_required' => true],
                    ['name_en' => 'Nature of business', 'name_fil' => 'Uri ng negosyo', 'details_en' => 'Short description of goods or services.', 'details_fil' => 'Maikling paglalarawan ng produkto o serbisyo.', 'is_required' => true],
                ],
            ],
            [
                ...$contact,
                'slug' => 'community-facility-reservation',
                'name_en' => 'Community Facility Reservation',
                'name_fil' => 'Reserbasyon ng Pasilidad ng Komunidad',
                'description_en' => 'Request a demonstration schedule for a fictional multipurpose hall or community meeting room.',
                'description_fil' => 'Humiling ng demonstrasyong iskedyul para sa kathang-isip na multipurpose hall o community meeting room.',
                'eligibility_en' => 'Residents and community groups planning a lawful, non-commercial local activity.',
                'eligibility_fil' => 'Mga residente at grupong pangkomunidad na nagpaplano ng legal at hindi komersyal na lokal na gawain.',
                'fee_centavos' => 50000,
                'processing_time_en' => '2 business days after schedule review',
                'processing_time_fil' => '2 araw ng trabaho matapos suriin ang iskedyul',
                'target_business_days' => 2,
                'office_hours_en' => 'Reservation desk: Monday to Friday, 9:00 AM to 4:00 PM',
                'office_hours_fil' => 'Reservation desk: Lunes hanggang Biyernes, 9:00 AM hanggang 4:00 PM',
                'procedure_steps_en' => ['Choose the facility and proposed date.', 'Describe the activity and expected attendance.', 'Wait for schedule confirmation.', 'Review the fictional facility-use reminders.'],
                'procedure_steps_fil' => ['Piliin ang pasilidad at mungkahing petsa.', 'Ilarawan ang gawain at inaasahang bilang ng dadalo.', 'Hintayin ang kumpirmasyon ng iskedyul.', 'Suriin ang kathang-isip na paalala sa paggamit ng pasilidad.'],
                'appointment_required' => true,
                'requirements' => [
                    ['name_en' => 'Activity details', 'name_fil' => 'Detalye ng gawain', 'details_en' => 'Purpose, proposed date and time, and expected attendance.', 'details_fil' => 'Layunin, mungkahing petsa at oras, at inaasahang bilang ng dadalo.', 'is_required' => true],
                    ['name_en' => 'Alternative schedule', 'name_fil' => 'Alternatibong iskedyul', 'details_en' => 'A second date helps resolve schedule conflicts.', 'details_fil' => 'Makakatulong ang pangalawang petsa kung may conflict sa iskedyul.', 'is_required' => false],
                ],
            ],
            [
                ...$contact,
                'slug' => 'local-concern-report',
                'name_en' => 'Local Concern Report',
                'name_fil' => 'Ulat ng Lokal na Alalahanin',
                'description_en' => 'Report a fictional neighborhood concern such as a damaged walkway, uncollected waste, or a streetlight issue.',
                'description_fil' => 'Mag-ulat ng kathang-isip na alalahanin sa lugar gaya ng sirang daanan, hindi nakolektang basura, o problema sa ilaw sa kalye.',
                'eligibility_en' => 'Anyone reporting a non-emergency concern located within fictional Barangay Haraya. For emergencies, contact the appropriate real emergency service instead.',
                'eligibility_fil' => 'Sinumang nag-uulat ng hindi emergency na alalahanin sa kathang-isip na Barangay Haraya. Para sa emergency, makipag-ugnayan sa angkop na tunay na emergency service.',
                'fee_centavos' => 0,
                'processing_time_en' => 'Acknowledgement within 1 business day; resolution time depends on the concern',
                'processing_time_fil' => 'Pagkilala sa loob ng 1 araw ng trabaho; nakadepende sa alalahanin ang oras ng pagresolba',
                'target_business_days' => 1,
                'office_hours_en' => 'Online reports accepted anytime; reviewed Monday to Friday, 8:00 AM to 5:00 PM',
                'office_hours_fil' => 'Tumatanggap ng online report anumang oras; sinusuri Lunes hanggang Biyernes, 8:00 AM hanggang 5:00 PM',
                'procedure_steps_en' => ['Confirm that the concern is not an emergency.', 'Describe the issue and general location.', 'Add an optional non-sensitive photo in a later project phase.', 'Use the secure reference to follow public updates.'],
                'procedure_steps_fil' => ['Tiyaking hindi emergency ang alalahanin.', 'Ilarawan ang problema at pangkalahatang lokasyon.', 'Magdagdag ng opsyonal at hindi sensitibong larawan sa susunod na phase.', 'Gamitin ang secure reference upang sundan ang pampublikong update.'],
                'appointment_required' => false,
                'requirements' => [
                    ['name_en' => 'Concern description', 'name_fil' => 'Paglalarawan ng alalahanin', 'details_en' => 'Explain what happened and when it was noticed.', 'details_fil' => 'Ipaliwanag kung ano ang nangyari at kailan ito napansin.', 'is_required' => true],
                    ['name_en' => 'General location', 'name_fil' => 'Pangkalahatang lokasyon', 'details_en' => 'Use a landmark or street; do not include private household details.', 'details_fil' => 'Gumamit ng palatandaan o kalye; huwag ilagay ang pribadong detalye ng tahanan.', 'is_required' => true],
                ],
            ],
        ];
    }
}
