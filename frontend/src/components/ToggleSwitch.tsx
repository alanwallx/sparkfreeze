import { useState } from "react";

interface Props {
  checked: boolean;
  onChange: (checked: boolean) => void;
}

export default function ToggleSwitch({checked, onChange}: Props) {
  return (
    <form>
      <input
        type="checkbox"
        checked = {checked}
        onChange={(e) => {
          onChange(e.target.checked);
        }}
      />
    </form>
  );
}


