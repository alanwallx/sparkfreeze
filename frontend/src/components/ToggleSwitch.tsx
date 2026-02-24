// import { useState } from "react";

interface Props {
  checked: boolean;
  onChange: (checked: boolean) => void;
  label?: string;
}

export default function ToggleSwitch({checked, onChange, label}: Props) {
  // get a unique id for the input element even if label is not provided
  const inputLabel = `toggle-switch-${Math.random().toString(36).substring(2, 15)}`;

  return (
    <form className={"toggle-switch"}>
      {label ? <label htmlFor={inputLabel}>{label}</label> : ""}
      <input
        id={inputLabel}
        type="checkbox"
        checked = {checked}
        onChange={(e) => {
          onChange(e.target.checked);
        }}
      />
    </form>
  );
}


