<?php
/**
 * Manages authorization & permissions.
 * 
 * Figures out if the user has some given permission or if they can access a
 * route.
 */
class Authorization {
    private $roles;
    private $route_permissions;
    private $testing_mode;

    private $permissions = null;

    public function __construct($roles, $route_permissions, $testing_mode) {
        $this->roles = $roles;
        $this->route_permissions = $route_permissions;
        $this->testing_mode = $testing_mode;
    }

    public function role() {
        return App::auth()->user()?->role() ?? 'public';
    }

    private function resolve_permissions($role = null, &$visited_roles = []) {
        if (is_null($role)) $role = $this->role();
        if (isset($visited_roles[$role])) return [];
        $visited_roles[$role] = true;
        $perms = [];
        foreach ($this->roles[$role]['inherits'] ?? [] as $superrole) {
            $perms += $this->resolve_permissions($superrole, $visited_roles);
        }
        foreach ($this->roles[$role]['permissions'] ?? [] as $perm) {
            $perms[$perm] = true;
        }
        return $perms;
    }

    public function has_permission($permission) {
        if (is_null($this->permissions))
            $this->permissions = $this->resolve_permissions();
        return isset($this->permissions[$permission]);
    }

    public function has_permissions($permissions) {
        foreach ($permissions as $permission) {
            if (!$this->has_permission($permission)) return false;
        }
        return true;
    }

    private function prefix_matches($prefix, $url) {
        if (str_starts_with($url, $prefix)) return true;
        if ($url === rtrim($prefix, '/')) return true;
        return false;
    }

    private function testing_mode_applies($type, $route) {
        if (!$this->testing_mode['enabled']) return false;
        foreach (['all', $type] as $scope) {
            foreach ($this->testing_mode['exempt_routes'][$scope] ?? []
                     as $prefix) {
                if ($this->prefix_matches($prefix, $route)) return false;
            }
        }
        return true;
    }

    private function route_permissions($type, $route) {
        $permissions = [];
        foreach (['all', $type] as $scope) {
            foreach ($this->route_permissions[$scope] ?? []
                     as $prefix => $perms) {
                if ($this->prefix_matches($prefix, $route)) {
                    foreach ($perms as $perm) $permissions[$perm] = true;
                }
            }
        }
        if ($this->testing_mode_applies($type, $route)) {
            foreach ($this->testing_mode['required_permissions'] as $perm)
                $permissions[$perm] = true;
        }
        return array_keys($permissions);
    }

    public function authorize_page($route) {
        return $this->has_permissions(
            $this->route_permissions('pages', $route)
        );
    }

    public function authorize_action($route) {
        return $this->has_permissions(
            $this->route_permissions('actions', $route)
        );
    }
}
?>
