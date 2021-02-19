import React, {useState, useRef, ReactElement, isValidElement} from 'react';
import styled, {css} from 'styled-components';
import {arrayUnique, Key, Override} from '../../../shared';
import {InputProps} from '../InputProps';
import {IconButton} from '../../../components';
import {useBooleanState, useShortcut} from '../../../hooks';
import {AkeneoThemedProps, getColor} from '../../../theme';
import {ArrowDownIcon} from '../../../icons';
import {ChipInput, ChipValue} from './ChipInput';

const MultiSelectInputContainer = styled.div<{value: string | null; readOnly: boolean} & AkeneoThemedProps>`
  & input[type='text'] {
    cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'pointer')};
    background: ${({value, readOnly}) => (null === value && readOnly ? getColor('grey', 20) : 'transparent')};

    &:focus {
      z-index: 2;
    }
  }
`;

const InputContainer = styled.div`
  position: relative;
`;

const ActionContainer = styled.div`
  position: absolute;
  right: 10px;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
`;

const OptionContainer = styled.div`
  background: ${getColor('white')};
  height: 34px;
  padding: 0 20px;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: ${getColor('grey', 120)};
  line-height: 34px;

  &:focus {
    color: ${getColor('grey', 120)};
  }
  &:hover {
    background: ${getColor('grey', 20)};
    color: ${getColor('brand', 140)};
  }
  &:active {
    color: ${getColor('brand', 100)};
    font-weight: 700;
  }
  &:disabled {
    color: ${getColor('grey', 100)};
  }
`;

const EmptyResultContainer = styled.div`
  background: ${getColor('white')};
  height: 20px;
  padding: 0 20px;
  align-items: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: ${getColor('grey', 100)};
  line-height: 20px;
  text-align: center;
`;

type VerticalPosition = 'up' | 'down';

const OverlayContainer = styled.div`
  position: relative;
`;

const Overlay = styled.div<{verticalPosition: VerticalPosition} & AkeneoThemedProps>`
  background: ${getColor('white')};
  box-shadow: 0 0 4px 0 rgba(0, 0, 0, 0.3);
  padding: 10px 0 10px 0;
  position: absolute;
  transition: opacity 0.15s ease-in-out;
  z-index: 2;
  left: 0;
  right: 0;

  ${({verticalPosition}) =>
    'up' === verticalPosition
      ? css`
          bottom: 46px;
        `
      : css`
          top: 6px;
        `};
`;

const Backdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
`;

const OptionCollection = styled.div`
  max-height: 320px;
  overflow-y: auto;
`;

type OptionProps = {
  value: string;
  children: string;
} & React.HTMLAttributes<HTMLSpanElement>;

const Option = ({children, ...rest}: OptionProps) => <span {...rest}>{children}</span>;

type MultiMultiSelectInputProps = Override<
  Override<React.InputHTMLAttributes<HTMLDivElement>, InputProps<string[]>>,
  (
    | {
        readOnly: true;
      }
    | {
        readOnly?: boolean;
        onChange: (newValue: string[]) => void;
      }
  ) & {
    /**
     * The props value of the selected option.
     */
    value: string[];

    /**
     * The placeholder displayed when no option is selected.
     */
    placeholder?: string;

    /**
     * The text displayed when no result was found.
     */
    emptyResultLabel: string;

    /**
     * Accessibility text for the open dropdown button.
     */
    openSelectLabel?: string;

    /**
     * Accessibility text for the remove chip button.
     */
    removeLabel: string;

    /**
     * Defines if the input is valid on not.
     */
    invalid?: boolean;

    /**
     * The options.
     */
    children?: ReactElement<OptionProps>[] | ReactElement<OptionProps>;

    /**
     * Force the vertical position of the overlay.
     */
    verticalPosition?: VerticalPosition;
  }
>;

/**
 * Multi select input allows the user to select content and data
 * when the expected user input is composed of multiple option values.
 */
const MultiSelectInput = ({
  id,
  placeholder,
  invalid,
  value,
  emptyResultLabel,
  children = [],
  onChange,
  removeLabel,
  openSelectLabel = '',
  readOnly = false,
  verticalPosition = 'down',
  'aria-labelledby': ariaLabelledby,
  ...rest
}: MultiMultiSelectInputProps) => {
  const [searchValue, setSearchValue] = useState<string>('');
  const [dropdownIsOpen, openOverlay, closeOverlay] = useBooleanState();
  const inputRef = useRef<HTMLInputElement>(null);

  const validChildren = React.Children.toArray(children).filter((child): child is ReactElement<OptionProps> =>
    isValidElement<OptionProps>(child)
  );

  const indexedChips = validChildren.reduce<{[key: string]: ChipValue}>(
    (indexedChips: {[key: string]: ChipValue}, child) => {
      if (typeof child.props.children !== 'string') {
        throw new Error('Multi select only accepts string as Option');
      }

      if (
        Object.values(indexedChips)
          .map(chip => chip.code)
          .includes(child.props.value)
      ) {
        throw new Error(`Duplicate option value ${child.props.value}`);
      }

      indexedChips[child.props.value] = {code: child.props.value, label: child.props.children};

      return indexedChips;
    },
    {}
  );

  const filteredChildren = validChildren.filter(child => {
    const childValue = child.props.value;
    const optionValue = childValue + child.props.children;

    if (value.includes(childValue)) {
      return false;
    }

    return -1 !== optionValue.toLowerCase().indexOf(searchValue.toLowerCase());
  });

  const chipValues = value.map(chipCode => indexedChips[chipCode]);

  const handleEnter = () => {
    if (filteredChildren.length > 0 && dropdownIsOpen) {
      const newValue = filteredChildren[0].props.value;

      onChange?.(arrayUnique([...value, newValue]));
      setSearchValue('');
      closeOverlay();
    }
  };

  const handleSearch = (value: string) => {
    setSearchValue(value);
    openOverlay();
  };

  const handleRemove = (chipsCode: string) => {
    onChange?.(value.filter(value => value !== chipsCode));
  };

  const handleClick = () => {
    if (dropdownIsOpen) {
      setSearchValue('');
      closeOverlay();
    } else {
      openOverlay();
    }
  };

  const handleOptionClick = (newValue: string) => () => {
    onChange?.(arrayUnique([...value, newValue]));
    setSearchValue('');
    closeOverlay();
    inputRef.current?.focus();
  };

  const handleBlur = () => {
    setSearchValue('');
    closeOverlay();
    inputRef.current?.blur();
  };

  useShortcut(Key.Enter, handleEnter, inputRef);
  useShortcut(Key.Escape, handleBlur, inputRef);

  return (
    <MultiSelectInputContainer readOnly={readOnly} value={value} {...rest}>
      <InputContainer>
        <ChipInput
          ref={inputRef}
          placeholder={placeholder}
          value={chipValues}
          searchValue={searchValue}
          removeLabel={removeLabel}
          readOnly={readOnly}
          invalid={invalid}
          onSearchChange={handleSearch}
          onRemove={handleRemove}
          onClick={handleClick}
        />
        {!readOnly && (
          <ActionContainer>
            <IconButton
              ghost="borderless"
              level="tertiary"
              size="small"
              icon={<ArrowDownIcon />}
              title={openSelectLabel}
              onClick={openOverlay}
              tabIndex={0}
            />
          </ActionContainer>
        )}
      </InputContainer>
      <OverlayContainer>
        {dropdownIsOpen && !readOnly && (
          <>
            <Backdrop data-testid="backdrop" onClick={handleBlur} />
            <Overlay verticalPosition={verticalPosition} onClose={handleBlur}>
              <OptionCollection>
                {filteredChildren.length === 0 ? (
                  <EmptyResultContainer>{emptyResultLabel}</EmptyResultContainer>
                ) : (
                  filteredChildren.map(child => {
                    const value = child.props.value;

                    return (
                      <OptionContainer key={value} onClick={handleOptionClick(value)}>
                        {React.cloneElement(child)}
                      </OptionContainer>
                    );
                  })
                )}
              </OptionCollection>
            </Overlay>
          </>
        )}
      </OverlayContainer>
    </MultiSelectInputContainer>
  );
};

Option.displayName = 'MultiSelectInput.Option';
MultiSelectInput.Option = Option;

export {MultiSelectInput};
