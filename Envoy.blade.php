@servers(['web' => 'deployer@ssh.myswimmingclub.co.uk'])

@setup
    $repository = 'git@gitlab.com:swimming-club-data-systems/Membership.git';
    $releases_dir = $baseDir . '/releases';
    $app_dir = $baseDir;
    $release = date('YmdHis');
    $new_release_dir = $releases_dir .'/'. $release;
    $v1_dir = $new_release_dir . '/src_v1';
    $v2_dir = $new_release_dir . '/src';
@endsetup

@story('deploy')
    clone_repository
    v2_run_composer
    v2_run_composer
    v2_build_front_end
    v1_build_front_end
    v2_cache
    update_symlinks
@endstory

@task('clone_repository')
    echo 'Cloning repository'
    [ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
    git clone --depth 1 -b {{ $branch }} {{ $repository }} {{ $new_release_dir }}
    cd {{ $new_release_dir }}
    git reset --hard {{ $commit }}
@endtask

@task('v2_run_composer')
    echo "V2 Composer Install ({{ $release }})"
    cd {{ $v2_dir }}
    composer install --optimize-autoloader --no-dev --prefer-dist --no-scripts -q -o
@endtask

@task('v1_run_composer')
    echo "V1 Composer Install ({{ $release }})"
    cd {{ $v1_dir }}
    composer install --prefer-dist --no-scripts -q -o
@endtask

@task('v2_build_front_end')
    echo "V2 Front End Install and Build ({{ $release }})"
    cd {{ $v2_dir }}
    npm install
    npm run build
@endtask

@task('v1_build_front_end')
    echo "V1 Front End Install and Build ({{ $release }})"
    cd {{ $v1_dir }}
    npm install
    npm run build-production
@endtask

@task('v2_cache')
    echo "V2 Deploy Caches ({{ $release }})"
    cd {{ $v2_dir }}
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
@endtask

@task('update_symlinks')
    echo "Linking storage directory"
    rm -rf {{ $v2_dir }}/storage
    ln -nfs {{ $app_dir }}/storage {{ $v2_dir }}/storage

    echo 'Linking .env file'
    ln -nfs {{ $app_dir }}/.env {{ $v2_dir }}/.env

    echo 'Linking current release'
    ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
@endtask

@task('list', ['on' => 'localhost'])
    ls ./../html -l
@endtask
