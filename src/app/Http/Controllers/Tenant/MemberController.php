<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\CountriesOfRepresentation;
use App\Business\Helpers\Money;
use App\Business\Helpers\PhoneNumber;
use App\Enums\Sex;
use App\Http\Controllers\Controller;
use App\Models\Tenant\ClubMembershipClass;
use App\Models\Tenant\EmergencyContact;
use App\Models\Tenant\ExtraFee;
use App\Models\Tenant\Member;
use App\Models\Tenant\MemberPhotography;
use App\Models\Tenant\Squad;
use App\Models\Tenant\SquadMove;
use App\Models\Tenant\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MemberController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAll', Member::class);

        /** @var User $user */
        $user = Auth::user();

        $members = null;

        if ($request->query('query')) {
            $members = Member::search($request->query('query'))->where('Tenant', tenant('ID'))->query(fn ($query) => $query->with(['squads' => function ($query) {
                $query->orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc');
            }]))->paginate(config('app.per_page'));
        } else {
            $members = Member::where('Active', 1)->orderBy('MForename', 'asc')->orderBy('MSurname', 'asc')->with(['squads' => function ($query) {
                $query->orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc');
            }])->paginate(config('app.per_page'));
        }

        return Inertia::render('Members/Index', [
            'members' => $members->onEachSide(3),
            'can_create' => $user->can('create', Member::class),
        ]);
    }

    private function membershipClassesForSelects()
    {
        $clubMembershipClass = ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::CLUB)->get();
        $ngb = ClubMembershipClass::where('Type', \App\Enums\ClubMembershipClassType::NGB)->get();

        $clubClasses = [];
        $ngbClasses = [];

        foreach ($clubMembershipClass as $class) {
            /** @var ClubMembershipClass $class */
            $clubClasses[] = [
                'value' => $class->ID,
                'name' => $class->Name,
            ];
        }

        foreach ($ngb as $class) {
            /** @var ClubMembershipClass $class */
            $ngbClasses[] = [
                'value' => $class->ID,
                'name' => $class->Name,
            ];
        }

        return [
            'ngb_membership_classes' => $ngbClasses,
            'club_membership_classes' => $clubClasses,
        ];
    }

    public function new()
    {
        $this->authorize('create', Member::class);

        return Inertia::render('Members/New', [
            ...$this->membershipClassesForSelects(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Member::class);

        $request->validate([
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'ngb_reg' => ['max:36'],
            'ngb_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                return $query
                    ->where('Type', \App\Enums\ClubMembershipClassType::NGB)
                    ->where('Tenant', tenant('id'));
            })],
            'club_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                return $query
                    ->where('Type', \App\Enums\ClubMembershipClassType::CLUB)
                    ->where('Tenant', tenant('id'));
            })],
            'sex' => ['required', Rule::enum(Sex::class)],
            'club_pays_ngb_fees' => ['boolean'],
            'club_pays_club_membership_fees' => ['boolean'],
        ]);

        $member = new Member([
            'MForename' => $request->string('first_name'),
            'MSurname' => $request->string('last_name'),
            'DateOfBirth' => $request->date('date_of_birth'),
            'ASANumber' => $request->string('ngb_reg'),
            'Gender' => $request->enum('sex', Sex::class),
        ]);

        $member->NGBCategory = $request->string('ngb_category');
        $member->ClubCategory = $request->string('club_category');
        $member->ASAPaid = $request->boolean('club_pays_ngb_fees');
        $member->ClubPaid = $request->boolean('club_pays_club_membership_fees');
        $member->save();

        return redirect()->route('members.show', ['member' => $member->MemberID]);
    }

    public function show(Member $member, Request $request)
    {
        $this->authorize('view', $member);

        /** @var User $user */
        $user = $request->user();

        $markdownOptions = [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ];

        return Inertia::render('Members/Show', [
            'id' => $member->MemberID,
            'name' => $member->name,
            'first_name' => $member->MForename,
            'last_name' => $member->MSurname,
            'date_of_birth' => $member->DateOfBirth->format('Y-m-d'),
            'age' => $member->age(),
            'country' => CountriesOfRepresentation::getCountryName($member->Country),
            'governing_body_registration_number' => $member->ASANumber,
            'sex' => $member->Gender,
            'gender' => $member->GenderDisplay ? $member->GenderIdentity : null,
            'pronouns' => $member->GenderDisplay ? $member->GenderPronouns : null,
            'display_gender_identity' => $member->GenderDisplay,
            'medical' => [
                'conditions' => Str::markdown($member->memberMedical?->Conditions, $markdownOptions),
                'allergies' => Str::markdown($member->memberMedical?->Allergies, $markdownOptions),
                'medication' => Str::markdown($member->memberMedical?->Medication, $markdownOptions),
                'gp_name' => $member->memberMedical?->GPName,
                'gp_phone' => $member->memberMedical?->GPPhone,
                'gp_address' => $member->memberMedical?->GPAddress ?? [],
                'consent_withheld' => $member->memberMedical?->WithholdConsent,
            ],
            'emergency_contacts' => $member->user?->emergencyContacts->map(function (EmergencyContact $contact) {
                $number = null;
                try {
                    $number = PhoneNumber::create($contact->ContactNumber);
                } catch (\Exception $e) {
                    // Swallow
                }

                return [
                    'id' => $contact->ID,
                    'name' => $contact->Name,
                    'relation' => $contact->Relation,
                    'contact_number_url' => $number?->toRfc() ?? 'tel:'.$contact->ContactNumber,
                    'contact_number_display' => $number?->toNational() ?? $contact->ContactNumber,
                ];
            }),
            'photography_permissions' => [
                'website' => $member->photographyPermissions?->Website ?? false,
                'social' => $member->photographyPermissions?->Social ?? false,
                'noticeboard' => $member->photographyPermissions?->Noticeboard ?? false,
                'film_training' => $member->photographyPermissions?->FilmTraining ?? false,
                'professional_photographer' => $member->photographyPermissions?->ProPhoto ?? false,
            ],
            'squads' => $member->squads->map(function (Squad $squad) {
                return [
                    'id' => $squad->SquadID,
                    'name' => $squad->SquadName,
                    'fee' => $squad->fee,
                    'formatted_fee' => Money::formatCurrency($squad->fee),
                    'pays' => $squad->pivot->Paying,
                ];
            }),
            'squad_moves' => $member->squadMoves->map(function (SquadMove $move) {
                return [
                    'id' => $move->ID,
                    'old_squad' => $move->oldSquad ? [
                        'id' => $move->oldSquad->SquadID,
                        'name' => $move->oldSquad->SquadName,
                    ] : null,
                    'new_squad' => $move->newSquad ? [
                        'id' => $move->newSquad->SquadID,
                        'name' => $move->newSquad->SquadName,
                    ] : null,
                    'paying' => $move->Paying,
                    'date' => $move->Date,
                ];
            }),
            'extra_fees' => $member->extraFees->map(function (ExtraFee $fee) {
                return [
                    'id' => $fee->ExtraID,
                    'name' => $fee->ExtraName,
                    'fee' => $fee->fee,
                    'formatted_fee' => Money::formatCurrency($fee->fee),
                    'type' => $fee->Type,
                ];
            }),
            'club_membership_class' => [
                'name' => $member->clubCategory->Name,
            ],
            'club_pays_club_membership_fee' => $member->ClubPaid,
            'governing_body_membership_class' => [
                'name' => $member->governingBodyCategory->Name,
            ],
            'club_pays_governing_body_membership_fee' => $member->ASAPaid,
            'other_notes' => Str::markdown($member->OtherNotes, $markdownOptions),
            'editable' => $user->can('update', $member),
            'deletable' => $user->can('delete', $member),
        ]);
    }

    public function edit(Member $member, Request $request)
    {
        $this->authorize('update', $member);

        /** @var User $user */
        $user = $request->user();

        $countries = [];
        foreach (CountriesOfRepresentation::all() as $code => $name) {
            $countries[] = [
                'value' => $code,
                'name' => $name,
            ];
        }

        return Inertia::render('Members/Edit', [
            'id' => $member->MemberID,
            'name' => $member->name,
            'first_name' => $member->MForename,
            'age' => $member->age(),
            'form_initial_values' => [
                'first_name' => $member->MForename,
                'last_name' => $member->MSurname,
                'date_of_birth' => $member->DateOfBirth->format('Y-m-d'),
                'country' => $member->Country,
                'ngb_reg' => $member->ASANumber,
                'ngb_category' => $member->NGBCategory,
                'club_category' => $member->ClubCategory,
                'club_pays_ngb_fees' => $member->ASAPaid,
                'club_pays_club_membership_fees' => $member->ClubPaid,
                'sex' => $member->Gender,
                'gender' => $member->GenderDisplay ? $member->GenderIdentity : null,
                'pronouns' => $member->GenderDisplay ? $member->GenderPronouns : null,
                'display_gender_identity' => $member->GenderDisplay,
                'other_notes' => $member->OtherNotes,
                'photo_website' => $member->photographyPermissions?->Website ?? false,
                'photo_social' => $member->photographyPermissions?->Social ?? false,
                'photo_noticeboard' => $member->photographyPermissions?->Noticeboard ?? false,
                'photo_film_training' => $member->photographyPermissions?->FilmTraining ?? false,
                'photo_professional_photographer' => $member->photographyPermissions?->ProPhoto ?? false,
            ],
            ...$this->membershipClassesForSelects(),
            'countries_of_representation' => $countries,
            'is_admin' => $user->hasPermission('Admin'),
            'is_linked_user' => $user->UserID === $member->UserID,
        ]);
    }

    public function update(Member $member, Request $request)
    {
        $this->authorize('update', $member);

        /** @var User $user */
        $user = $request->user();

        $isLinkedUser = $user->UserID === $member->UserID;

        $genericValidationRules = [
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'sex' => ['required', Rule::enum(Sex::class)],
            'gender' => ['string', 'nullable', 'max:256'],
            'pronouns' => ['string', 'nullable', 'max:256'],
            'display_gender_identity' => ['boolean'],
            'other_notes' => ['string', 'nullable', 'max:8192'],
            'country' => ['required', Rule::in(CountriesOfRepresentation::getISOKeys())],
        ];

        $adminValidationRules = [];
        $photoValidationRules = [];

        if ($user->hasPermission('Admin')) {
            $adminValidationRules = [
                'ngb_reg' => ['max:36'],
                'ngb_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                    return $query
                        ->where('Type', \App\Enums\ClubMembershipClassType::NGB)
                        ->where('Tenant', tenant('id'));
                })],
                'club_category' => ['required', 'uuid', Rule::exists('clubMembershipClasses', 'ID')->where(function (Builder $query) {
                    return $query
                        ->where('Type', \App\Enums\ClubMembershipClassType::CLUB)
                        ->where('Tenant', tenant('id'));
                })],
                'club_pays_ngb_fees' => ['boolean'],
                'club_pays_club_membership_fees' => ['boolean'],
            ];
        }

        if ($isLinkedUser) {
            $photoValidationRules = [
                'photo_website' => ['boolean'],
                'photo_social' => ['boolean'],
                'photo_noticeboard' => ['boolean'],
                'photo_professional_photographer' => ['boolean'],
                'photo_film_training' => ['boolean'],
            ];
        }

        $request->validate([
            ...$genericValidationRules,
            ...$adminValidationRules,
            ...$photoValidationRules,
        ]);

        $member->MForename = $request->string('first_name');
        $member->MSurname = $request->string('last_name');
        $member->DateOfBirth = $request->date('date_of_birth');
        $member->Gender = $request->enum('sex', Sex::class);
        $member->GenderIdentity = $request->string('gender');
        $member->GenderPronouns = $request->string('pronouns');
        $member->GenderDisplay = $request->boolean('display_gender_identity');
        $member->Country = $request->string('country');

        if ($user->hasPermission('Admin')) {
            $member->ASANumber = $request->string('ngb_reg');
            $member->NGBCategory = $request->string('ngb_category');
            $member->ClubCategory = $request->string('club_category');
            $member->ASAPaid = $request->boolean('club_pays_ngb_fees');
            $member->ClubPaid = $request->boolean('club_pays_club_membership_fees');
        }

        if ($isLinkedUser && $member->age() < 18) {
            // Handle photography permissions update
            $permissions = $member->photographyPermissions ?? new MemberPhotography();

            $permissions->Website = $request->boolean('photo_website');
            $permissions->Social = $request->boolean('photo_social');
            $permissions->Noticeboard = $request->boolean('photo_noticeboard');
            $permissions->ProPhoto = $request->boolean('photo_professional_photographer');
            $permissions->FilmTraining = $request->boolean('photo_film_training');

            $member->photographyPermissions()->save($permissions);
        }

        $member->save();

        $request->session()->flash('success', $member->MForename.'\'s details have been updated successfully.');

        return redirect()->route('members.show', $member);
    }

    public function combobox(Request $request): \Illuminate\Http\JsonResponse
    {
        $members = null;
        if ($request->query('query')) {
            $members = Member::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        }

        $membersArray = [];

        $selectedMember = null;
        if ($request->query('id')) {
            /** @var Member $selectedMember */
            $selectedMember = Squad::find($request->query('id'));
            if ($selectedMember) {
                $membersArray[] = [
                    'id' => $selectedMember->MemberID,
                    'name' => $selectedMember->name,
                ];
            }
        }

        if ($members) {
            foreach ($members as $member) {
                /** @var Member $member */
                if ($selectedMember == null || $selectedMember->MemberID !== $member->MemberID) {
                    $membersArray[] = [
                        'id' => $member->MemberID,
                        'name' => $member->name,
                    ];
                }
            }
        }

        $responseData = [
            'data' => $membersArray,
            'has_more_pages' => $members && $members->hasMorePages(),
            'total' => $members ? $members->total() : count($membersArray),
        ];

        return \response()->json($responseData);
    }

    public function squads(Member $member)
    {
        $this->authorize('view', $member);

        $data = $member->squads->map(function (Squad $squad) {
            return [
                'value' => $squad->SquadID,
                'name' => $squad->SquadName,
            ];
        });

        return \response()->json($data);
    }
}
