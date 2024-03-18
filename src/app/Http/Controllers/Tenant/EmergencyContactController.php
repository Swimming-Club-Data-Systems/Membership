<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Tenant\EmergencyContact;
use App\Models\Tenant\User;
use App\Rules\ValidPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmergencyContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $contacts = $user->emergencyContacts()->paginate(config('app.per_page'));

        $contacts->getCollection()->transform(function (EmergencyContact $item) {
            $number = $item->ContactNumber;
            $url = 'tel:'.$item->ContactNumber;

            try {
                $phone = PhoneNumber::create($item->ContactNumber);

                $number = $phone->toNational();
                $url = $phone->toRfc();
            } catch (\Exception $e) {
            }

            return [
                'id' => $item->ID,
                'name' => $item->Name,
                'relation' => $item->Relation,
                'phone_plain' => $item->ContactNumber,
                'phone' => $number,
                'phone_url' => $url,
            ];
        });

        return Inertia::render('EmergencyContacts/Index', [
            'contacts' => $contacts,
        ]);

        return response()->json($contacts);
    }

    public function create(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'max:255'],
            'relation' => ['required', 'max:255'],
            'phone' => ['required', 'max:255', new ValidPhone],
        ]);

        $emergencyContact = new EmergencyContact();
        $emergencyContact->Name = $request->string('name');
        $emergencyContact->Relation = $request->string('relation');
        $emergencyContact->ContactNumber = PhoneNumber::toDatabaseFormat($request->string('phone'));

        $user->emergencyContacts()->save($emergencyContact);

        $request->session()->flash('success', 'New emergency contact saved successfully.');

        return redirect()->back();
    }

    public function update(Request $request, EmergencyContact $contact)
    {
        $this->authorize('update', $contact);

        /** @var User $user */
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'max:255'],
            'relation' => ['required', 'max:255'],
            'phone' => ['required', 'max:255', new ValidPhone],
        ]);

        $contact->Name = $request->string('name');
        $contact->Relation = $request->string('relation');
        $contact->ContactNumber = PhoneNumber::toDatabaseFormat($request->string('phone'));

        $user->emergencyContacts()->save($contact);

        $request->session()->flash('success', $contact->Name.' updated successfully.');

        return redirect()->back();
    }

    public function delete(Request $request, EmergencyContact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        $request->session()->flash('success', $contact->Name.' deleted successfully.');

        return redirect()->back();
    }
}
