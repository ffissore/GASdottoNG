@foreach(App\Role::rolesByClass(get_class($object)) as $role)
    <div class="form-group">
        <label class="col-sm-{{ $labelsize }} control-label">{{ $role->name }}</label>

        <div class="col-sm-{{ $fieldsize - 1 }}">
            <label class="static-label">
                <?php

                $final = [];

                foreach($role->users as $user) {
                    if($user->roles()->where('roles.id', $role->id)->first()->applies($supplier))
                        $final[] = $user->printableName();
                }

                ?>

                {{ join(', ', $final) }}
            </label>
        </div>
    </div>
@endforeach
